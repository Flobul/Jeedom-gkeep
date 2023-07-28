PROGRESS_FILE=/tmp/jeedom/gkeep/dependency
if [ ! -z $1 ]; then
	PROGRESS_FILE=$1
fi

VENV_DIR=$2/venv

touch ${PROGRESS_FILE}
echo 0 > ${PROGRESS_FILE}
echo "********************************"
echo "*Launch install of dependencies*"
echo "********************************"
echo $(date)
echo 5 > ${PROGRESS_FILE}
apt-get clean
echo 10 > ${PROGRESS_FILE}
apt-get update
echo 30 > ${PROGRESS_FILE}

echo "*****************************"
echo "Install modules using apt-get"
echo "*****************************"
apt-get install -y python3 python3-requests python3-pip python3-setuptools python3-venv
echo 40 > ${PROGRESS_FILE}

echo "*************************************"
echo "Creating python 3 virtual environment"
echo "*************************************"
python3 -m venv $VENV_DIR
echo 50 > ${PROGRESS_FILE}
echo "Done"

echo "*************************************"
echo "Install the required python libraries"
echo "*************************************"
$VENV_DIR/bin/python3 -m pip install --upgrade pip wheel
echo 60 > ${PROGRESS_FILE}
$VENV_DIR/bin/python3 -m pip install "urllib3<2"
echo 80 > ${PROGRESS_FILE}
$VENV_DIR/bin/python3 -m pip install gkeepapi


echo "*********************************"
echo "Get version of installed packages"
echo "*********************************"
echo 90 > ${PROGRESS_FILE}
$VENV_DIR/bin/python3 --version
pip list

echo 100 > ${PROGRESS_FILE}
echo $(date)
echo "***************"
echo "*Install ended*"
echo "***************"
rm ${PROGRESS_FILE}
