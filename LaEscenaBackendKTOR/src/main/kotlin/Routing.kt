package KtorLaEscena


import KtorLaEscena.database.UserRepository
import io.ktor.server.application.*
import io.ktor.server.request.*
import io.ktor.server.response.*
import io.ktor.server.routing.*

// Asegúrate de que estas clases estén en el mismo paquete o impórtalas si están en otro
// import KtorLaEscena.LoginRequest
// import KtorLaEscena.LoginResponse

fun Route.loginRoute() {
    post("/login") {
        try {
            // Capturamos el objeto JSON directamente
            val loginData = call.receive<LoginRequest>()

            // Llamamos al repositorio para validar las credenciales
            val role = UserRepository.login(loginData.email, loginData.password)

            if (role != null) {
                // Login exitoso
                call.respond(LoginResponse(
                    success = true,
                    message = "Login exitoso",
                    role = role
                ))
            } else {
                // Credenciales incorrectas
                call.respond(LoginResponse(
                    success = false,
                    message = "Credenciales inválidas"
                ))
            }
        } catch (e: Exception) {
            // Error de formato (ej. JSON mal formado o falta Content-Type: application/json)
            call.respond(LoginResponse(
                success = false,
                message = "Error en el formato de datos: ${e.localizedMessage}"
            ))
        }
    }
}