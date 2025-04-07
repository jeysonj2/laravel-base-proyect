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
- Creación de dos endpoints para la gestión del perfil del usuario en sesión:
  - Un endpoint para obtener los datos del usuario autenticado.
  - Un endpoint para actualizar los datos del usuario autenticado, con las siguientes restricciones:
    - No permite cambiar la contraseña a través de este endpoint.
    - No permite cambiar el rol del usuario.
    - Si se cambia el email, se envía automáticamente un correo de verificación al nuevo email.
- Implementación de un sistema de bloqueo de cuentas de usuario tras múltiples intentos fallidos de inicio de sesión:
  - Bloqueo temporal después de ingresar incorrectamente la contraseña más de 3 veces en un periodo de 5 minutos (valores configurables mediante variables de entorno `MAX_LOGIN_ATTEMPTS` y `LOGIN_ATTEMPTS_WINDOW_MINUTES`).
  - El bloqueo temporal dura 1 hora por defecto (configurable mediante la variable de entorno `ACCOUNT_LOCKOUT_DURATION_MINUTES`).
  - Si un usuario es bloqueado temporalmente 2 veces dentro de un periodo de 24 horas, se bloquea indefinidamente (valores configurables mediante variables de entorno `MAX_LOCKOUTS_IN_PERIOD` y `LOCKOUT_PERIOD_HOURS`).
  - Cuando un usuario es bloqueado, recibe un correo electrónico indicando la duración del bloqueo y la opción de contactar a un administrador.
  - Creación de endpoints para que los administradores puedan ver la lista de usuarios bloqueados y desbloquearlos.
- Implementación de un sistema estandarizado de respuestas JSON para todos los controladores:
  - Creación de una clase `ApiResponse` en `app/Http/Responses` que encapsula la lógica para generar respuestas consistentes.
  - Implementación de un trait `ApiResponseTrait` que proporciona métodos convenientes como `successResponse`, `errorResponse`, `unauthorizedResponse`, etc.
  - Integración del trait en el controlador base para que todos los controladores hereden automáticamente estos métodos.
  - Estandarización del formato de respuesta con campos `code`, `message` y `data` (opcional) para mantener la consistencia.
  - Personalización de la gestión de errores de desarrollo, mostrando información de depuración solo en entorno de desarrollo.
- Implementación de documentación PHPDoc completa para todos los archivos del proyecto:
  - Documentación detallada de todos los modelos con sus propiedades, relaciones y métodos.
  - Documentación exhaustiva de todos los controladores, incluyendo parámetros, tipos de retorno y descripciones de funcionalidad.
  - Documentación de los servicios, middlewares, eventos, listeners y clases de correo.
  - Documentación de los archivos de configuración y bootstrap.
  - Documentación de las rutas API, web y console.
  - Documentación del sistema de respuestas API estandarizadas.
  - Todo siguiendo los estándares oficiales de PHPDoc para asegurar compatibilidad con herramientas de generación de documentación y facilitar el desarrollo futuro.
- Actualización del archivo README.md del proyecto:
  - Creación de un README profesional con información detallada sobre el proyecto.
  - Inclusión de badges informativos sobre la versión de Laravel, PHP 8.4, JWT, PostgreSQL y Docker.
  - Documentación clara de todas las características y funcionalidades del proyecto.
  - Instrucciones de instalación y configuración detalladas para un entorno Docker.
  - Lista completa de endpoints API organizados por categoría y funcionalidad.
  - Documentación de las variables de entorno importantes para la configuración del proyecto.
- Agregados tests faltantes

**Notas importantes:**

- Estamos utilizando Docker Compose durante el desarrollo, por lo que todo se ejecuta en contenedores y no directamente desde la terminal. Cualquier comando que necesite ejecutarse debe realizarse dentro de los contenedores. Por favor lee el archivo docker-compose.yml para que tengas en el contexto los nombres de los servicios.
- Estamos trabajando con Laravel 12, por lo que todas las soluciones deben estar adaptadas a esta versión. Si es necesario, consulta la documentación oficial de Laravel 12 en <https://laravel.com/docs/12.x>.
- Las variables de entorno se obtienen como cadenas de texto (strings), por lo que es necesario realizar un casting explícito a otros tipos de datos según sea necesario.
- Nosotros nos estaremos comunicando en español, sin embargo, todo el código generado, junto con los mensajes y comentarios, debe estar en inglés para cumplir con los estándares internacionales.
- Al agregar una nueva funcionalidad por favor agrega los tests relacionados
- Al modificar alguna funcionalidad por favor modifica los tests relacionados de ser necesario
- cada ves que es te escriba `probado y aprobado` tu vas a actualizar el archivo `Laravel - Proyecto Base.md` con lo que se haya hecho recientemente y el archivo `README.md` de ser necesario

Por favor, confirma si estás listo para recibir la siguiente tarea.
