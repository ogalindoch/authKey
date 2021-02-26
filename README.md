# AuthKey
## Parte del servidor REST para EUROGLAS
Autenticación de clientes, usando Llaves pre-compartidas

### Archivos

    eurorest
    ├───src
    │   └───authkey.php
    ├───.gitignore
    ├───composer.json
    ├───index.php
    ├───iniciaServidorDePruebas.php
    ├───servidor.ini
    ├───LICENSE
    └───README.md

### Directorio `src`

Contiene el archivo que implementan el modulo, extendiendo la clase base `auth` (que implementa las interfaces `authInterface` y `restModuleInterface`)

```C#
class authkey extends \euroglas\eurorest\auth
```
### Directorio Raiz

 Contiene los archivos para pruebas del modulo.


| Archivo  | Descripcion   |
|---|---|
| .gitIgnore | blah |
| composer.json| Manejo de requerimientos |
| index.php | Implementacion del servidor de pruebas
| servidor.ini | Configuracion del servidor | 
| iniciaServidorDePruebas.bat | Script para arrancar el servidor usando el servidor interno de PHP |
| LICENSE | Licencia de uso de este paquete |
| README .md | éste archivo |

## Configuración

| Llave  | Explicación   |
|---|---|
| ServerName="" | Nombre del servidor | 
| ModoDebug = 1 | Habilita el modo de desarrollo | 
| [Modulos] | Grupo de Modulos a habilitar |
| authkey=1 | Habilita el modulo authkey queda registrado como proveedor de Auth

## Token

Para el servidor, estamos haciendo uso de los JASON Web Token [(JWT)](https://jwt.io/introduction). 
Para usarlos, se requieren DOS pasos:
1. Intercambiar credenciales (la llave) por un token.  
   1. Se hace una solicitud al servidor, incluyendo las credenciales requeridas
   2. El servidor valida las credenciales y si son validas, devuelve un TOKEN
   3. Si las credenciales NO son validas, se regresa un error
2. Todas las llamadas que requieran token, deben incluirlo:   
    Encabezado HTTP: `Authorization`   
    Formato : `Bearer <token>`  (OJO: Es sensible a mayuscula/minuscula)

## URLs

| Metodo | URL | TOKEN | Descripción   |
|---|---|---|---|
| POST | /auth | NO | Intercambia credenciales por un Token JWT. <br> Como credenciales, se espera recibir un parametro `key` con la llave precompartida del cliente, ya sea como parte del *body* o como parametro *GET* <br> Ejemplo: `ejemplo.com?key={LLAVE}` |
| GET | /testoken | SI  | Valida el token proporcionado |
| GET | /auth/name | SI | Nombre del cliente autenticado |


#
# Uso del modulo authkey en un servidor REST de EUROGLAS

- Instala composer en tu ambiente de desarrollo
- Crea un directorio para tu proyecto
- Agrega el modulo base (eurorest) a tu proyecto:   
```bash
composer require euroglas/eurorest
```
- Agrega éste modulo
```bash
composer require euroglas/authkey
```
- O bien, edita el archivo `composer.json` para que contenga los requerimientos
```json
        {
            "require": {
                "euroglas/eurorest": "^1.0.0",
                "euroglas/authkey": "^1.0.0"
            }
        }
```
- Ejecuta composer para instalar las dependencias (esto va a generar el archivo `composer.lock` y el directorio `vendor`)
```
php composer.phar install
```
Si quieres hacer uso de la utileria de autocarga de clases de composer, incluye esto en tu script php (`index.php`):
```php
require 'vendor/autoload.php';
```

**NOTA** Si quieres saber más sobre el servidor REST, consulta la informacion del modulo [euroglas/eurorest](https://github.com/ogalindoch/eurorest)

