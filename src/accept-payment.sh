#!/bin/bash

HOST="oscar.koeroo.net"
HOST="opcyberpaint.nl"
CONTROLLER_URL="https://${HOST}/inschrijving/controller.php"


authorize_payment() {

AUTHKEY="$1"
PAYMENTTOKEN="$2"

curl \
	-dpaymenttoken="${PAYMENTTOKEN}" \
	-dauthkey="${AUTHKEY}" \
        "${CONTROLLER_URL}"

}


if [ -z "${BASH_VERSION}" ]; then
    echo "Not running in Bash, please use bash"
    exit 1
fi

echo -n "Payment Token: "
read PAYMENTTOKEN
if [ -z ${PAYMENTTOKEN} ]; then
    echo "Payment token needed!"
    exit 1
fi

echo -n "Authorization key: "
read -s AUTHKEY
if [ -z ${AUTHKEY} ]; then
    echo "error: no authorization key provided"
    exit 1
fi

authorize_payment "${AUTHKEY}" "${PAYMENTTOKEN}"

