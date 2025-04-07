# Contexto inicial para GitHub Copilot Agent Mode

Continuaremos con el proyecto en Laravel 12. Hasta ahora hemos logrado lo siguiente:

- Inicialización del proyecto con Laravel 12.
- Configuración de Docker Compose para ejecutar el proyecto.
- Configuración de la base de datos en PostgreSQL.
- Migración y creación de modelos para las tablas de usuarios y roles.
- Creación de controladores y endpoints CRUD para usuarios y roles.
- Todas las respuestas se generan en formato JSON, incluidas las excepciones.
- Implementación de un validador personalizado para garantizar que un campo sea único, sin importar mayúsculas o minúsculas.
- Creación de un controlador para la autenticación, con endpoints para login y refresh token.
- Uso de JWT para la gestión de sesiones, utilizando la librería `tymon/jwt-auth`.
- Protección de las rutas de usuarios y roles mediante la gestión de sesiones; solo los usuarios con un token JWT válido pueden acceder.
- Implementación de un middleware para restringir el acceso a las rutas según el rol del usuario en sesión.
- Restricción de todas las rutas de usuarios y roles para que solo puedan ser accedidas por usuarios con el rol de 'admin'.
- Creación de un endpoint para enviar un correo electrónico al usuario con un código de verificación para confirmar la validez de su correo.
- Creación de un endpoint para verificar el correo electrónico del usuario, que recibe un código para validar el email.
- Configuración para que, al crear o editar un usuario, se envíe automáticamente el correo de verificación para confirmar la cuenta.
- Configuración de Mailpit en Docker Compose para realizar pruebas de envío de correos.
- Implementación de dos endpoints para el restablecimiento de contraseña:
  - Un endpoint para solicitar el restablecimiento, que envía un correo con un token único.
  - Un endpoint para verificar el token y establecer una nueva contraseña.
- Configuración del envío de un correo de confirmación cuando la contraseña se cambia exitosamente.
- Adición de una variable de entorno `PASSWORD_RESET_TOKEN_EXPIRY_MINUTES` para controlar el tiempo de expiración del token.
- Validación de contraseñas nuevas mediante la regla `strong_password`, que requiere al menos una letra mayúscula, un número y un carácter especial.
- Implementación de un endpoint de logout que invalida el token de acceso.
- Implementación de un endpoint para que el usuario autenticado pueda cambiar su propia contraseña.

**Notas importantes:**

- Estamos utilizando Docker Compose durante el desarrollo, por lo que todo se ejecuta en contenedores y no directamente desde la terminal. Cualquier comando que necesite ejecutarse debe realizarse dentro de los contenedores.
- Estamos trabajando con Laravel 12, por lo que todas las soluciones deben estar adaptadas a esta versión. Si es necesario, consulta la documentación oficial de Laravel 12 en <https://laravel.com/docs/12.x>.
- Las variables de entorno se obtienen como cadenas de texto (strings), por lo que es necesario realizar un casting explícito a otros tipos de datos según sea necesario.
- Aunque nos comunicamos en español, todo el código generado, junto con los mensajes y comentarios, debe estar en inglés para cumplir con los estándares internacionales.

Por favor, confirma si estás listo para recibir la siguiente tarea.
