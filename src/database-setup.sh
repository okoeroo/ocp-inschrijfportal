#!/bin/bash

TMPFILE="/tmp/tmp.create.db"

create_db() {

DBNAME="$1"
USER_USER="$2"
USER_PASS="$3"
ROOT_PASS="$4"

echo "DROP DATABASE IF EXISTS ${DBNAME};" | mysql -uroot -p"$ROOT_PASS"
echo "DROP USER '${USER_USER}'@'localhost';" | mysql -uroot -p"$ROOT_PASS"
echo "CREATE DATABASE $DBNAME;" | mysql -uroot -p"$ROOT_PASS"
echo "CREATE USER '${USER_USER}'@'localhost' IDENTIFIED BY '${USER_PASS}';" | mysql -uroot -p"$ROOT_PASS"
echo "GRANT SELECT,INSERT,UPDATE,DELETE ON ${DBNAME}.* TO '${USER_USER}'@'localhost';" | mysql -uroot -p"$ROOT_PASS"
echo "FLUSH PRIVILEGES;" | mysql -uroot -p"$ROOT_PASS"

cat > ${TMPFILE} << EOF
USE ${DBNAME}

CREATE TABLE spelers (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        nickname VARCHAR(50) NOT NULL,
        realname VARCHAR(100),
        email VARCHAR(500) NOT NULL,
        faction VARCHAR(50),
        gotgame VARCHAR(50),
        dieet VARCHAR(50),
        dieetoverige VARCHAR(500),
        status VARCHAR(50),
        authkey VARCHAR(256),
        paymenttoken VARCHAR(128),
        grouptoken VARCHAR(128),
        doortoken VARCHAR(128),
        paid VARCHAR(16),
        created_on TIMESTAMP DEFAULT 0,
        changed_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ON UPDATE CURRENT_TIMESTAMP
    );

CREATE UNIQUE INDEX index_authkey ON spelers (authkey);
EOF
mysql -uroot -p"${ROOT_PASS}" < ${TMPFILE}


cat > ${TMPFILE} << EOF
USE ${DBNAME}

CREATE TABLE controllers (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        authkey VARCHAR(256),
	owner VARCHAR(255),
        payment VARCHAR(1),
        view VARCHAR(1),
        door VARCHAR(1)
    );

CREATE UNIQUE INDEX index_authkey_c ON controllers (authkey);
EOF
mysql -uroot -p"${ROOT_PASS}" < ${TMPFILE}

cat > ${TMPFILE} << EOF
USE ${DBNAME}

CREATE TABLE sessions (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        token VARCHAR(256) NOT NULL,
        valid_for_seconds INT NOT NULL,
        created_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
EOF
mysql -uroot -p"${ROOT_PASS}" < ${TMPFILE}
}


echo -n "Database name [inschrijvingen]: "
read DBNAME
if [ -z ${DBNAME} ]; then
    DBNAME="inschrijvingen"
fi

echo -n "user username [userinsch]: "
read USER_USER
if [ -z ${USER_USER} ]; then
    USER_USER="userinsch"
fi

echo -n "user user password: "
read -s USER_PASS
if [ -z ${USER_PASS} ]; then
    echo "error: no password provided"
    exit 1
fi
echo
echo "------------"
echo -n "Provide MySQL root pwd: "
read -s ROOT_PASS
if [ -z ${ROOT_PASS} ]; then
    echo "error: no password provided"
    exit 1
fi
echo

create_db "${DBNAME}" "${USER_USER}" "${USER_PASS}" "${ROOT_PASS}"

