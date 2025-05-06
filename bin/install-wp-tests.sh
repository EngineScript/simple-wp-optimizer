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
            curl -s "$1" -o "$2" && return 0
            echo "Attempt $i failed, retrying in 3 seconds..."
            sleep 3
        done
        echo "Error: Failed to download $1 after 3 attempts"
        exit 1
    elif command -v wget >/dev/null 2>&1; then
        echo "Downloading $1 using wget..."
        for i in {1..3}; do
            wget -nv -O "$2" "$1" && return 0
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

	echo "Downloading extra MySQL database driver..."
	download https://raw.github.com/markoheijnen/wp-mysqli/master/db.php $WP_CORE_DIR/wp-content/db.php
	
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
		mkdir -p $WP_TESTS_DIR
		svn co --quiet https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/includes/ $WP_TESTS_DIR/includes
		svn co --quiet https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/data/ $WP_TESTS_DIR/data
	fi

	if [ ! -f wp-tests-config.php ]; then
		download https://develop.svn.wordpress.org/${WP_TESTS_TAG}/wp-tests-config-sample.php "$WP_TESTS_DIR"/wp-tests-config.php
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
		exit 1
	fi
}
	
	# Verify test environment was created successfully
	if [ ! -d "$WP_TESTS_DIR/includes" ] || [ ! -f "$WP_TESTS_DIR/wp-tests-config.php" ]; then
		echo "Error: WordPress test environment setup failed. Missing required files."
		exit 1
	fi
}
		sed $ioption "s/youremptytestdbnamehere/$DB_NAME/" "$WP_TESTS_DIR"/wp-tests-config.php
		sed $ioption "s/yourusernamehere/$DB_USER/" "$WP_TESTS_DIR"/wp-tests-config.php
		sed $ioption "s/yourpasswordhere/$DB_PASS/" "$WP_TESTS_DIR"/wp-tests-config.php
		sed $ioption "s|localhost|${DB_HOST}|" "$WP_TESTS_DIR"/wp-tests-config.php
	fi

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

install_wp
install_test_suite
install_db

echo "WordPress test environment setup complete."
echo "Log file available at: $LOG_FILE"

# Final verification of test environment
if [ ! -d "$WP_TESTS_DIR/includes" ] || [ ! -f "$WP_TESTS_DIR/wp-tests-config.php" ]; then
	echo "Error: WordPress test environment setup failed. Missing required files."
	exit 1
fi

echo "Success: WordPress test environment is ready."
