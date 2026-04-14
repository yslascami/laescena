package KtorLaEscena.routes

import KtorLaEscena.Users
import KtorLaEscena.Artistas        // ← agregar este import
import KtorLaEscena.RegisterRequest
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
            val signupData = call.receive<RegisterRequest>()

            // 1. Verificar si el correo ya está registrado
            val existe = transaction {
                Users.select { Users.email eq signupData.email }.singleOrNull()
            }
            if (existe != null) {
                call.respond(HttpStatusCode.Conflict, "El correo ya está registrado")
                return@post
            }

            // 2. Insertar en users Y artistas en la misma transacción
            transaction {
                Users.insert {
                    it[Users.email]    = signupData.email
                    it[Users.password] = signupData.password
                    it[Users.role]     = signupData.role
                }

                // Solo crear perfil en artistas si el rol es "artista"
                if (signupData.role == "artista") {
                    Artistas.insert {
                        it[Artistas.nombre]   = signupData.nombre   // asegúrate que RegisterRequest tenga este campo
                        it[Artistas.correo]   = signupData.email
                        it[Artistas.aprobado] = 0                   // pendiente de aprobación
                    }
                }
            }

            call.respond(HttpStatusCode.Created, "Usuario registrado correctamente")

        } catch (e: Exception) {
            call.respond(HttpStatusCode.BadRequest, "Error al registrar: ${e.localizedMessage}")
        }
    }
}