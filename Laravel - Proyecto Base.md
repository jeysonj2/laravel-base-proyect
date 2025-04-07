# Contexto inicial para Github Copilot Agent Mode

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
- Se configuró Mailpit con el docker compose para las pruebas de envío de correos
- Se implementaron dos end-points para el restablecimiento de contraseña:
  - Un endpoint para solicitar el restablecimiento que envía un email con un token único
  - Un endpoint para verificar el token y establecer una nueva contraseña
- Se configuró el envío de un email de confirmación cuando la contraseña se cambia exitosamente
- Se agregó una variable de entorno `PASSWORD_RESET_TOKEN_EXPIRY_MINUTES` para controlar el tiempo de expiración del token
- Las contraseñas nuevas deben cumplir con la validación de `strong_password` que requiere al menos una letra mayúscula, un número y un carácter especial

Siempre recuerda que:

- Estamos usando docker compose durante el desarrollo, así que todo se está ejecutando en contenedores y no desde mi terminal, así que cualquier comando que necesite ejecutar debe ser dentro de los contenedores
- Estamos usando Laravel 12, así que todas las soluciones que se vayan a aplicar debe ser teniendo en cuenta que son para la versión 12 de Laravel, si lo requieres por favor accede a la documentación de Laravel 12 que está en <https://laravel.com/docs/12.x>
- Cuando trabajes con variables de entorno, ten en cuenta que estas se obtienen como strings, por lo que deberás hacer cast explícito a entero u otros tipos según sea necesario
- Nosotros chateamos en español, pero todo el código generado junto con los mensajes y comentarios debe ser en inglés para seguir el estándar internacional

Por favor confirma si estás listo para darte la siguiente tarea.
