Vamos a continuar con el proyecto en Laravel 12, hasta ahora llevamos:

- Inicialización del proyecto con Laravel 12
- Configuración de Docker compose para ejecutar el proyecto
- Configuración de la base de datos en Postgres
- Migración y Modelos de tablas de usuario y roles de usuario
- Creación de controladores y end-points CRUD para usuarios y roles
- Todas las respuestas son en JSON, incluyendo las excepciones
- Se creó un validador personalizado para validar que un campo sea único sin importar mayúsculas o minúsculas
- Se creó un controlador para la autenticación con los end-points de login y refresh token
- Para el manejo de la sesión se está usando JWT, con la libreria tymon/jwt-auth
- Se aseguraron las rutas de users y roles con el manejo de sesión, solo usuarios con una sesión válida pueden acceder, es decir, con un token JWT válido
- Se agrega middleware para restringir el acceso a las rutas dependiendo del role del usuario de la sessión
- Se restringieron todas las rutas de usuarios y roles para que solo las puedan acceder los usuarios con role 'admin'
- Se creó un end-point para enviar un email al usuario con un código para que confirme que su correo es válido
- Se creó un end-point que se usa para verificar el email del usuario, este recibe un codigo que se usa para validar el email
- Cuando se crea o edita un usuario se envía automáticamente el email de verificación para que el usuario confirme que es su cuenta de correo

Siempre recuerda que:
- Estamos usando docker compose durante el desarrollo, asi que todo se está ejecutando en contenedores y no desde mi terminal, asi que cualquier comando que necesite ejecutar debe ser dentro de los contenedores
- Estamos usando Laravel 12, asi que todas las soluciones que se vayan a aplicar debe ser teniendo en cuenta que son para la versión 12 de Laravel, si lo requieres por favor accede a la documentación de Laravel 12 que está en https://laravel.com/docs/12.x
- Nosotros chateamos en español, pero todo el código generado junto con los mensajes y comentarios debe ser en inglés para seguir el estandar internacional

Por favor confirmame si estas listo para darte la siguiente tarea.
