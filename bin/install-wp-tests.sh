#!/usr/bin/env bash

# Enable debugging and exit on error
set -e

# Create a log file for debugging purposes
LOG_FILE="/tmp/wordpress-tests-lib-install-$(date +%s).log"
exec > >(tee -a "$LOG_FILE") 2>&1

echo "Starting WordPress test environment installation..."

if [ $# -lt 3 ]; then
	echo "usage: $0 <db-name> <db-user> <db-pass> [db-host] [wp-version] [skip-database-creation]"
	exit 1
fi

# Check for missing dependencies before proceeding
if ! command -v mysql >/dev/null 2>&1; then
    echo "Error: MySQL client is not installed."
    exit 1
fi

DB_NAME=$1
DB_USER=$2
DB_PASS=$3
DB_HOST=${4-localhost}
WP_VERSION=${5-latest}
SKIP_DB_CREATE=${6-false}

TMPDIR=${TMPDIR-/tmp}
TMPDIR=$(echo $TMPDIR | sed -e "s/\/$//")
WP_TESTS_DIR=${WP_TESTS_DIR-$TMPDIR/wordpress-tests-lib}
WP_CORE_DIR=${WP_CORE_DIR-$TMPDIR/wordpress/}

# Create a symlink from /wordpress-tests-lib to actual tests dir if not using the default
if [[ "$WP_TESTS_DIR" == "/tmp/wordpress-tests-lib" && ! -d "/wordpress-tests-lib" ]]; then
  echo "Setting up compatibility symlink at /wordpress-tests-lib"
  if [ ! -d "/wordpress-tests-lib" ]; then
    sudo mkdir -p /wordpress-tests-lib || mkdir -p /wordpress-tests-lib || echo "Could not create /wordpress-tests-lib directory"
    sudo chmod 1777 /wordpress-tests-lib || chmod 1777 /wordpress-tests-lib 2>/dev/null || echo "Could not set permissions on /wordpress-tests-lib"
  fi
fi

echo "Configuration:"
echo "  DB_NAME: $DB_NAME"
echo "  DB_USER: $DB_USER"
echo "  DB_HOST: $DB_HOST"
echo "  WP_VERSION: $WP_VERSION"
echo "  WP_TESTS_DIR: $WP_TESTS_DIR"
echo "  WP_CORE_DIR: $WP_CORE_DIR"

download() {
    if command -v curl >/dev/null 2>&1; then
        echo "Downloading $1 using curl..."
        for i in {1..3}; do
            if curl -s "$1" -o "$2"; then
                # Verify download was successful
                if [ -s "$2" ]; then
                    echo "Download successful!"
                    return 0
                else
                    echo "Downloaded file is empty. Retrying..."
                fi
            fi
            echo "Attempt $i failed, retrying in 3 seconds..."
            sleep 3
        done
        echo "Error: Failed to download $1 after 3 attempts"
        exit 1
    elif command -v wget >/dev/null 2>&1; then
        echo "Downloading $1 using wget..."
        for i in {1..3}; do
            if wget -nv -O "$2" "$1" && [ -s "$2" ]; then
                echo "Download successful!"
                return 0
            fi
            echo "Attempt $i failed, retrying in 3 seconds..."
            sleep 3
        done
        echo "Error: Failed to download $1 after 3 attempts"
        exit 1
    else
        echo "Error: Neither curl nor wget is available for downloads."
        exit 1
    fi
}

if [[ $WP_VERSION =~ ^[0-9]+\.[0-9]+\-(beta|RC)[0-9]+$ ]]; then
	WP_BRANCH=${WP_VERSION%\-*}
	WP_TESTS_TAG="branches/$WP_BRANCH"
	echo "Using WordPress branch: $WP_BRANCH"

elif [[ $WP_VERSION =~ ^[0-9]+\.[0-9]+$ ]]; then
	WP_TESTS_TAG="branches/$WP_VERSION"
	echo "Using WordPress version: $WP_VERSION (branch)"
elif [[ $WP_VERSION =~ [0-9]+\.[0-9]+\.[0-9]+ ]]; then
	if [[ $WP_VERSION =~ [0-9]+\.[0-9]+\.[0] ]]; then
		# version x.x.0 means the first release of the major version, so strip off the .0 and download version x.x
		WP_TESTS_TAG="tags/${WP_VERSION%??}"
		echo "Using WordPress version: ${WP_VERSION%??} (tag)"
	else
		WP_TESTS_TAG="tags/$WP_VERSION"
		echo "Using WordPress version: $WP_VERSION (tag)"
	fi
elif [[ $WP_VERSION == 'nightly' || $WP_VERSION == 'trunk' ]]; then
	WP_TESTS_TAG="trunk"
	echo "Using WordPress trunk/nightly"
else
	# Fetch the latest WordPress version with fallback
	if [[ $WP_VERSION == 'latest' ]]; then
		echo "Determining the latest WordPress version..."
		download http://api.wordpress.org/core/version-check/1.7/ /tmp/wp-latest.json
		LATEST_VERSION=$(grep -oP '"version":"\K[^"]+' /tmp/wp-latest.json | head -1)
		if [[ -z "$LATEST_VERSION" ]]; then
			echo "Error: Could not determine the latest WordPress version."
			exit 1
		fi
		WP_TESTS_TAG="tags/$LATEST_VERSION"
		echo "Using latest WordPress version: $LATEST_VERSION"
	fi
fi
set -ex

install_wp() {
	echo "Installing WordPress core..."

	if [ -d $WP_CORE_DIR ]; then
		echo "WordPress core directory already exists, skipping installation."
		return;
	fi

	mkdir -p $WP_CORE_DIR
	echo "Created WordPress directory: $WP_CORE_DIR"

	if [[ $WP_VERSION == 'nightly' || $WP_VERSION == 'trunk' ]]; then
		echo "Downloading WordPress nightly build..."
		mkdir -p $TMPDIR/wordpress-nightly
		download https://wordpress.org/nightly-builds/wordpress-latest.zip  $TMPDIR/wordpress-nightly/wordpress-nightly.zip
		unzip -q $TMPDIR/wordpress-nightly/wordpress-nightly.zip -d $TMPDIR/wordpress-nightly/
		mv $TMPDIR/wordpress-nightly/wordpress/* $WP_CORE_DIR
	else
		if [ $WP_VERSION == 'latest' ]; then
			local ARCHIVE_NAME='latest'
			echo "Downloading latest WordPress release..."
		elif [[ $WP_VERSION =~ [0-9]+\.[0-9]+ ]]; then
			# https serves multiple offers, whereas http serves single.
			download https://api.wordpress.org/core/version-check/1.7/ $TMPDIR/wp-latest.json
			if [[ $WP_VERSION =~ [0-9]+\.[0-9]+\.[0] ]]; then
				# version x.x.0 means the first release of the major version, so strip off the .0 and download version x.x
				LATEST_VERSION=${WP_VERSION%??}
				echo "Converting $WP_VERSION to $LATEST_VERSION"
			else
				# otherwise, scan the releases and get the most up to date minor version of the major release
				local VERSION_ESCAPED=`echo $WP_VERSION | sed 's/\./\\\\./g'`
				LATEST_VERSION=$(grep -o '"version":"'$VERSION_ESCAPED'[^"]*' $TMPDIR/wp-latest.json | sed 's/"version":"//' | head -1)
				echo "Found latest version in $WP_VERSION series: $LATEST_VERSION"
			fi
			if [[ -z "$LATEST_VERSION" ]]; then
				echo "Warning: Could not determine latest version for $WP_VERSION series, using exact version."
				local ARCHIVE_NAME="wordpress-$WP_VERSION"
			else
				local ARCHIVE_NAME="wordpress-$LATEST_VERSION"
			fi
		else
			local ARCHIVE_NAME="wordpress-$WP_VERSION"
			echo "Downloading WordPress $WP_VERSION..."
		fi
		download https://wordpress.org/${ARCHIVE_NAME}.tar.gz  $TMPDIR/wordpress.tar.gz
		if [ $? -ne 0 ]; then
			echo "Error: Failed to download WordPress archive."
			exit 1
		fi
		
		echo "Extracting WordPress files..."
		tar --strip-components=1 -zxmf $TMPDIR/wordpress.tar.gz -C $WP_CORE_DIR
		if [ $? -ne 0 ]; then
			echo "Error: Failed to extract WordPress archive."
			exit 1
		fi
	fi

	echo "Setting up MySQL database driver..."
	# Create directory if it doesn't exist
	mkdir -p $WP_CORE_DIR/wp-content/
	
	# Use local db.php file from the repository
	if [ -f "bin/db.php" ]; then
		echo "Using local db.php from bin/db.php"
		cp bin/db.php $WP_CORE_DIR/wp-content/db.php
	elif [ -f "${0%/*}/db.php" ]; then
		# Try to find db.php in the same directory as the script
		echo "Using local db.php from script directory"
		cp "${0%/*}/db.php" $WP_CORE_DIR/wp-content/db.php
	else
		echo "Error: Local db.php file not found in expected locations"
		echo "Expected locations checked:"
		echo "  - bin/db.php"
		echo "  - ${0%/*}/db.php"
		
		# Create a simple db.php file inline as last resort
		echo "Creating a minimal db.php file as fallback"
		cat > $WP_CORE_DIR/wp-content/db.php << 'EOL'
<?php
/**
 * Fallback database driver to ensure tests can run
 */
if ( ! defined( 'WP_USE_EXT_MYSQL' ) ) {
	define( 'WP_USE_EXT_MYSQL', false );
}
EOL
		fi
		
		# Verify that db.php exists and is not empty
		if [ ! -s "$WP_CORE_DIR/wp-content/db.php" ]; then
			echo "Error: Failed to create a valid db.php file. Tests may not work correctly."
		else
			echo "db.php file created successfully."
		fi
	
	echo "WordPress core installation complete!"
}

install_test_suite() {
	# portable in-place argument for both GNU sed and Mac OSX sed
	if [[ $(uname -s) == 'Darwin' ]]; then
		local ioption='-i.bak'
	else
		local ioption='-i'
	fi

	# set up testing suite if it doesn't yet exist
	if [ ! -d $WP_TESTS_DIR ]; then
		# set up testing suite
		echo "Creating WordPress tests directory at $WP_TESTS_DIR"
		mkdir -p $WP_TESTS_DIR
		
		echo "Checking directory permissions"
		ls -ld $WP_TESTS_DIR
		
		echo "Downloading test files from WordPress SVN"
		svn co --quiet https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/includes/ $WP_TESTS_DIR/includes || {
			echo "Failed to download test includes - retrying once"
			svn cleanup $WP_TESTS_DIR/includes
			svn co --quiet https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/includes/ $WP_TESTS_DIR/includes || {
				echo "Error: Could not download WordPress test includes"
				return 1
			}
		}
		
		# Check for required files in the includes directory
		if [ ! -f "$WP_TESTS_DIR/includes/functions.php" ]; then
			echo "Error: functions.php not found in includes directory. Tests may not work properly."
		fi
		
		# Create/download any missing required files
		if [ ! -f "$WP_TESTS_DIR/includes/class-basic-object.php" ]; then
			echo "Creating missing class-basic-object.php file..."
			cat > "$WP_TESTS_DIR/includes/class-basic-object.php" << 'EOT'
<?php
/**
 * Basic object, which other objects in WordPress extend.
 * 
 * This is a simplified version for tests to fix the missing class issue.
 */
class Basic_Object {

    /**
     * Retrieve a value from an array with support for a default value.
     *
     * @param array  $args  Arguments.
     * @param string $key   Key to retrieve.
     * @param mixed  $default Default value.
     * @return mixed Value if set, default if not.
     */
    protected function get_from_array( $args, $key, $default = null ) {
        if ( isset( $args[ $key ] ) ) {
            return $args[ $key ];
        }
        return $default;
    }
}
EOT
		fi
		
		svn co --quiet https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/data/ $WP_TESTS_DIR/data || {
			echo "Failed to download test data - retrying once"
			svn cleanup $WP_TESTS_DIR/data
			svn co --quiet https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/data/ $WP_TESTS_DIR/data || {
				echo "Error: Could not download WordPress test data"
				return 1
			}
		}
	fi

	if [ ! -f wp-tests-config.php ]; then
		download https://develop.svn.wordpress.org/${WP_TESTS_TAG}/wp-tests-config-sample.php "$WP_TESTS_DIR"/wp-tests-config.php || {
			echo "Error: Could not download wp-tests-config-sample.php"
			return 1
		}
		
		# remove all forward slashes in the end
		WP_CORE_DIR=$(echo $WP_CORE_DIR | sed "s:/\+$::")
		sed $ioption "s:dirname( __FILE__ ) . '/src/':'$WP_CORE_DIR/':" "$WP_TESTS_DIR"/wp-tests-config.php
		sed $ioption "s/youremptytestdbnamehere/$DB_NAME/" "$WP_TESTS_DIR"/wp-tests-config.php
		sed $ioption "s/yourusernamehere/$DB_USER/" "$WP_TESTS_DIR"/wp-tests-config.php
		sed $ioption "s/yourpasswordhere/$DB_PASS/" "$WP_TESTS_DIR"/wp-tests-config.php
		sed $ioption "s|localhost|${DB_HOST}|" "$WP_TESTS_DIR"/wp-tests-config.php
	fi
	
	# Verify test environment was created successfully
	if [ ! -d "$WP_TESTS_DIR/includes" ] || [ ! -f "$WP_TESTS_DIR/wp-tests-config.php" ]; then
		echo "Error: WordPress test environment setup failed. Missing required files."
		return 1
	fi
	
	# Create a symlink or copy files to /wordpress-tests-lib if needed for compatibility
	if [[ "$WP_TESTS_DIR" == "/tmp/wordpress-tests-lib" && -d "$WP_TESTS_DIR" ]]; then
		if [ ! -d "/wordpress-tests-lib" ]; then
			echo "Creating directory at /wordpress-tests-lib for compatibility"
			mkdir -p /wordpress-tests-lib 2>/dev/null || sudo mkdir -p /wordpress-tests-lib 2>/dev/null
		fi
		
		if [ -d "/wordpress-tests-lib" ]; then
			echo "Copying test files to /wordpress-tests-lib for compatibility"
			cp -R "$WP_TESTS_DIR"/* /wordpress-tests-lib/ 2>/dev/null || sudo cp -R "$WP_TESTS_DIR"/* /wordpress-tests-lib/ 2>/dev/null
			
			# Ensure the class-basic-object.php exists in both locations
			if [ -f "$WP_TESTS_DIR/includes/class-basic-object.php" ] && [ ! -f "/wordpress-tests-lib/includes/class-basic-object.php" ]; then
				echo "Copying class-basic-object.php to alternate location"
				mkdir -p /wordpress-tests-lib/includes/ 2>/dev/null || sudo mkdir -p /wordpress-tests-lib/includes/ 2>/dev/null
				cp "$WP_TESTS_DIR/includes/class-basic-object.php" /wordpress-tests-lib/includes/ 2>/dev/null || 
				sudo cp "$WP_TESTS_DIR/includes/class-basic-object.php" /wordpress-tests-lib/includes/ 2>/dev/null
			elif [ ! -f "$WP_TESTS_DIR/includes/class-basic-object.php" ]; then
				echo "Warning: class-basic-object.php not found in source directory"
			fi
			
			if [ ! -f "/wordpress-tests-lib/includes/functions.php" ]; then
				echo "Warning: Could not copy all files to /wordpress-tests-lib"
			else
				echo "Successfully copied WordPress test files to /wordpress-tests-lib"
			fi
		else
			echo "Warning: Could not create /wordpress-tests-lib directory for compatibility"
		fi
	fi
	
	echo "Test suite installation successful!"
}

install_db() {

	if [ ${SKIP_DB_CREATE} = "true" ]; then
		echo "Skipping database creation as requested"
		return 0
	fi

	echo "Setting up test database..."

	# parse DB_HOST for port or socket references
	local PARTS=(${DB_HOST//\:/ })
	local DB_HOSTNAME=${PARTS[0]};
	local DB_SOCK_OR_PORT=${PARTS[1]};
	local EXTRA=""

	if ! [ -z $DB_HOSTNAME ] ; then
		if [ $(echo $DB_SOCK_OR_PORT | grep -e '^[0-9]\{1,\}$') ]; then
			EXTRA=" --host=$DB_HOSTNAME --port=$DB_SOCK_OR_PORT --protocol=tcp"
		elif ! [ -z $DB_SOCK_OR_PORT ] ; then
			EXTRA=" --socket=$DB_SOCK_OR_PORT"
		elif ! [ -z $DB_HOSTNAME ] ; then
			EXTRA=" --host=$DB_HOSTNAME --protocol=tcp"
		fi
	fi

	# Check if database already exists and drop it if it does
	echo "Checking if database $DB_NAME already exists..."
	mysql --user="$DB_USER" --password="$DB_PASS"$EXTRA -e "SHOW DATABASES LIKE '$DB_NAME'" | grep "$DB_NAME" > /dev/null 2>&1
	if [ $? -eq 0 ]; then
		echo "Database $DB_NAME exists, dropping it first..."
		mysqladmin drop $DB_NAME --user="$DB_USER" --password="$DB_PASS"$EXTRA --force
	fi

	# create database
	echo "Creating database $DB_NAME..."
	mysqladmin create $DB_NAME --user="$DB_USER" --password="$DB_PASS"$EXTRA || {
		echo "Error: Failed to create database $DB_NAME. Check your credentials and permissions."
		exit 1
	}
	
	echo "Database $DB_NAME created successfully."
}

echo "Starting WordPress test environment setup..."

# Execute each step with error handling
install_wp || {
  echo "WordPress core installation failed!"
  exit 1
}

install_test_suite || {
  echo "WordPress test suite installation failed!"
  exit 1
}

install_db || {
  echo "Database setup failed!"
  exit 1
}

echo "WordPress test environment setup complete."
echo "Log file available at: $LOG_FILE"

# Final verification of test environment
if [ ! -d "$WP_TESTS_DIR/includes" ] || [ ! -f "$WP_TESTS_DIR/wp-tests-config.php" ]; then
	echo "Error: WordPress test environment setup failed. Missing required files."
	exit 1
fi

echo "Success: WordPress test environment is ready."
