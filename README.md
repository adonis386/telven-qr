# Sistema de Cupones - Tienda Milenium

Sistema de gestión de cupones y descuentos para Tienda Milenium.

## Requisitos

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Servidor web (Apache recomendado)
- XAMPP (recomendado para desarrollo)

## Instalación

1. Clonar el repositorio:
```bash
git clone https://github.com/adonis386/telven-qr.git
```

2. Copiar los archivos a la carpeta de XAMPP:
```bash
cp -r telven-qr/* C:/xampp/htdocs/tiendaqr/
```

3. Configurar la base de datos:
   - Abrir `includes/config.php`
   - Modificar las credenciales de la base de datos si es necesario

4. Acceder a la página de instalación:
   - Abrir en el navegador: `http://localhost/tiendaqr/install.php`
   - Seguir las instrucciones de instalación

5. Verificar la instalación:
   - Acceder a: `http://localhost/tiendaqr/`
   - Generar QR: `http://localhost/tiendaqr/generate_qr.php`
   - Panel admin: `http://localhost/tiendaqr/admin/`

## Estructura de Directorios

- `admin/` - Panel de administración
- `assets/` - Recursos estáticos
- `includes/` - Archivos de configuración y funciones
- `uploads/` - Directorio para imágenes subidas

## Características

- Generación de códigos QR para registro de clientes
- Sistema de cupones con múltiples descuentos
- Panel de administración para validación de cupones
- Registro de clientes con foto y documento
- Gestión de descuentos y beneficios

## Seguridad

- Asegúrate de configurar correctamente los permisos de los directorios
- Modifica las credenciales por defecto en `config.php`
- Mantén actualizado el sistema y sus dependencias

## Soporte

Para reportar problemas o sugerir mejoras, por favor crear un issue en el repositorio. 