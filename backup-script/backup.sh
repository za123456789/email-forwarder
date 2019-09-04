#!/bin/bash
###################
#
#prerequisite :> keys file
#
###################

DATE=$(date +"%Y-%m-%d")
source "/etc/keys"
export AWS_ACCESS_KEY_ID
export AWS_SECRET_ACCESS_KEY

mkdir -p ${BACKUP_DIR}/${DATE}

backup_mysql(){
		cd ${DC_DIR}
		${DOCKER_COMPOSE} exec db \
        mysqldump -h ${MYSQL_HOST} \
           -P ${MYSQL_PORT} \
           -u ${MYSQL_USER} \
           -p${MYSQL_PASSWORD} ${DATABASE_NAME} | gzip > ${BACKUP_DIR}/${DATE}/${DATABASE_NAME}.sql.gz
}

backup_files(){
        tar -Pcvzf ${BACKUP_DIR}/${DATE}/backup-files.tar.gz ${DC_DIR}${FILES_TO_BACKUP[@]}
}

upload_s3(){
	echo ${BACKUP_DIR}/${DATE}
        aws s3 cp ${BACKUP_DIR}/${DATE} ${AMAZON_S3_BUCKET}${DATE} --recursive
}

backup_mysql
backup_files
upload_s3