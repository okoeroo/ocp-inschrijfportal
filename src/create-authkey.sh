#!/bin/bash


store_authkey() {

DBNAME="$1"
ROOT_PASS="$2"
AUTHKEY="$3"
OWNER="$4"
PAYMENT="$5"
VIEW="$6"
DOOR="$7"

mysql -uroot -p"${ROOT_PASS}" "${DBNAME}" -e "INSERT INTO controllers (authkey, owner, payment, view, door) VALUES (\"${AUTHKEY}\", \"${OWNER}\", \"${PAYMENT}\", \"${VIEW}\", \"${DOOR}\")"

return $?
}


echo -n "Database name [inschrijvingen]: "
read DBNAME
if [ -z ${DBNAME} ]; then
    DBNAME="inschrijvingen"
fi

echo -n "Provide MySQL root pwd: "
read -s ROOT_PASS
if [ -z ${ROOT_PASS} ]; then
    echo "error: no password provided"
    exit 1
fi
echo

echo -n "Owner name: "
read OWNER
if [ -z ${OWNER} ]; then
    echo "error: no name provided"
    exit 1
fi

echo -n "Auth Key (just press enter for auto-generation): "
read AUTHKEY 
if [ -z ${AUTHKEY} ]; then
    AUTHKEY=$(openssl rand -hex 64)
    echo "Generated Auth Key for ${OWNER}: ${AUTHKEY}"
fi

PAYMENT="n"
while true; do
    echo -n "Authorized to confirm payments? [y/N]: "
    read PAYMENT
    if [ -z ${PAYMENT} ]; then
        PAYMENT="n"
    fi
    if [ "${PAYMENT}" = "y" ] || [ "${PAYMENT}" = "Y" ] || [ "${PAYMENT}" = "n" ] || [ "${PAYMENT}" = "N" ]; then
        break
    fi
done

VIEW="n"
while true; do
    echo -n "Authorized to view the database? [y/N]: "
    read VIEW
    if [ -z ${VIEW} ]; then
        VIEW="n"
    fi
    if [ "${VIEW}" != "y" ] || [ "${VIEW}" != "Y" ] || [ "${VIEW}" != "n" ] || [ "${VIEW}" != "N" ]; then
        break
    fi
done

DOOR=""
while true; do
    echo -n "Authorized to confirm doortokens? [y/N]: "
    read DOOR
    if [ -z ${DOOR} ]; then
        DOOR="n"
    fi
    if [ "${DOOR}" != "y" ] || [ "${DOOR}" != "Y" ] || [ "${DOOR}" != "n" ] || [ "${DOOR}" != "N" ]; then
        break
    fi
done


store_authkey "${DBNAME}" "${ROOT_PASS}" "${AUTHKEY}" "${OWNER}" "${PAYMENT}" "${VIEW}" "${DOOR}"
exit $?

