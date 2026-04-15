package KtorLaEscena

import database.UserRepository
import io.ktor.server.application.*
import io.ktor.server.request.*
import io.ktor.server.response.*
import io.ktor.server.routing.*

fun Route.loginRoute() {
    post("/login") {
        try {
            val loginData = call.receive<LoginRequest>()
            val result: Pair<Int, String>? = UserRepository.login(loginData.email, loginData.password)

            if (result != null) {
                val (id, role) = result
                call.respond(LoginResponse(
                    success = true,
                    message = "Login exitoso",
                    id = id,
                    role = role
                ))
            } else {
                call.respond(LoginResponse(
                    success = false,
                    message = "Credenciales inválidas"
                ))
            }
        } catch (e: Exception) {
            call.respond(LoginResponse(
                success = false,
                message = "Error en el formato de datos: ${e.localizedMessage}"
            ))
        }
    }
}
