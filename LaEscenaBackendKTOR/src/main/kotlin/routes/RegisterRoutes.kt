package KtorLaEscena.routes

import KtorLaEscena.Users
import KtorLaEscena.RegisterRequest // Importamos el nuevo modelo
import io.ktor.http.*
import io.ktor.server.application.*
import io.ktor.server.response.*
import io.ktor.server.request.*
import io.ktor.server.routing.*
import org.jetbrains.exposed.sql.*
import org.jetbrains.exposed.sql.transactions.transaction

fun Route.registerRoute() {
    post("/register") {
        try {
            // Recibimos el objeto JSON
            val signupData = call.receive<RegisterRequest>()

            transaction {
                Users.insert {
                    it[Users.email] = signupData.email
                    it[Users.password] = signupData.password
                    it[Users.role] = signupData.role
                }
            }
            call.respond(HttpStatusCode.Created, "Usuario registrado correctamente")
        } catch (e: Exception) {
            call.respond(HttpStatusCode.BadRequest, "Error al registrar: ${e.localizedMessage}")
        }
    }
}