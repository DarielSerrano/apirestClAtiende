#	instalar ubuntu server 18.04 bionic beaver server, instalación normal
#	realizar instalación y actualización de dependencias
sudo apt update -y; sudo apt upgrade -y

#	instalar dependencias generales
sudo apt install apache2 php libapache2-mod-php git wget tar gcc make php-mysql -y

#	iniciar Apache
sudo service apache2 start

#	verificar que apache esté funcionando correctamente
sudo service apache2 status

#	(	reiniciar apache	)
#	(	sudo service apache2 restart	)

#	dependencia de pdftotext
sudo apt install poppler-utils -y

#	dependencias para python
sudo apt-get install zlib1g-dev libffi-dev libssl-dev libsqlite3-dev -y

#	instalacion python 3.10
- cd ~
- wget https://www.python.org/ftp/python/3.10.6/Python-3.10.6.tgz 
- tar -xf Python-3.10.6.tgz 
- cd Python-3.10.6 
- ./configure --enable-optimizations 
- make -j$(nproc) 
- sudo make altinstall 
- python3.10 --version

# (	Exclusivo para desarrollo y pruebas	)

#	actualizacion pip
pip install --upgrade pip

#	librerias de python necesarias para el DESARROLLO nlp
pip install 'transformers[torch]' sentencepiece protobuf stanza

#	luego CLONAR con git 
cd /var/www/html ; sudo git clone https://github.com/DarielSerrano/apirestClAtiende.git

#	para ACTUALIZAR utilizar repositorio
cd /var/www/html/apirestClAtiende/ && sudo git pull


# ( PRODUCCION	)

#	cambiar propietario a apache
sudo mkdir archivos
sudo chown www-data:www-data archivos
sudo chmod 700 archivos

#	crear y dar permisos a apache para las carpetas archivos, cache, librerias y cache para su administracion
cd /var/www/html/apirestClAtiende/

sudo mkdir cache
sudo mkdir librerias
sudo mkdir stanza_resources

sudo chown www-data:www-data archivos
sudo chown www-data:www-data logs_de_error.txt
sudo chown www-data:www-data cache
sudo chown www-data:www-data librerias
sudo chown www-data:www-data stanza_resources

sudo chmod 700 archivos
sudo chmod 744 logs_de_error.txt
sudo chmod 755 cache
sudo chmod 755 librerias
sudo chmod 755 stanza_resources

# (	para que Apache, como servidor web, pueda acceder a las librerias en la integracion y ejecucion, debe instalar los paquetes de pip con: sudo -u www-data pip y dirigirlos a una carpeta especifica 	)
sudo -u www-data pip install 'transformers[torch]' sentencepiece protobuf stanza --target=librerias

#	Utilizar en scripts de python administrados por apache
#	Para probar por comando, simulando a apache: 
#	sudo -u www-data STANZA_RESOURCES_DIR=stanza_resources python3.10 paquetes/extraer.py archivos/prueba.txt 
#	sudo -u www-data TRANSFORMERS_CACHE=cache python3.10 paquetes/resumir.py archivos/prueba.txt

#	codigos para hacer una prueba de funcionamiento en php y descargar librerias de uso dentro de scripts para NLP
cd /var/www/html/apirestClAtiende && STANZA_RESOURCES_DIR=stanza_resources python3.10 paquetes/extraer.py archivos/prueba.txt
cd /var/www/html/apirestClAtiende && TRANSFORMERS_CACHE=cache python3.10 paquetes/resumir.py archivos/prueba.txt

#	modificar en php.ini los campos upload_max_filesize y post_max_size a 100M por ejemplo para permitir archivos hasta 100M: 
sudo nano /etc/php/7.4/apache2/php.ini
upload_max_filesize=100M
post_max_size=100M

#	acceder a logs de error de apache en caso de fallos y no quedar en logs_de_error.txt
sudo cat /var/log/apache2/error.log