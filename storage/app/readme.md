
para el servidor se instaló el ffmpeg en el servidor 

INSTALACIÓN DE FFMPEG EN CENTOS 7

Si ffmpeg no está disponible en los repositorios de paquetes predeterminados de CentOS 7, puedes instalarlo utilizando otras opciones, como habilitar repositorios adicionales o instalarlo desde fuentes externas.

Aquí hay algunos métodos alternativos que podrías intentar:

1. Habilitar el repositorio RPM Fusion (opción recomendada):
El repositorio RPM Fusion proporciona paquetes multimedia, incluyendo ffmpeg, que podrías necesitar. Puedes habilitarlo e instalar ffmpeg utilizando estos comandos:

Habilitar RPM Fusion:
bashCopy code
sudo yum install epel-release sudo yum localinstall --nogpgcheck https://download1.rpmfusion.org/free/el/rpmfusion-free-release-7.noarch.rpm https://download1.rpmfusion.org/nonfree/el/rpmfusion-nonfree-release-7.noarch.rpm

Instalar ffmpeg:
bashCopy code
sudo yum install ffmpeg

sudo yum install ffprobe

2. Compilar e instalar desde las fuentes de FFmpeg:
Puedes compilar e instalar FFmpeg manualmente desde las fuentes. Esto implica descargar el código fuente, compilarlo y luego instalarlo. Es un proceso más avanzado pero te permite obtener la última versión disponible.

Para hacer esto, necesitarás descargar el código fuente de FFmpeg desde el sitio web oficial (Download FFmpeg ) y seguir las instrucciones de compilación proporcionadas en la documentación.