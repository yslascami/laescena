package KtorLaEscena.routes

import KtorLaEscena.database.ArtistaRepository
import io.ktor.http.*
import io.ktor.server.application.*
import io.ktor.server.response.*
import io.ktor.server.routing.*

fun Application.artistasRoutes() {
    routing {
        get("/artistas") {
            call.respond(ArtistaRepository.getAll())
        }

        get("/artistas/{id}") {
            val id = call.parameters["id"]?.toIntOrNull()
            if (id == null) {
                call.respond(HttpStatusCode.BadRequest, "ID inválido")
                return@get
            }
            val artista = ArtistaRepository.getById(id)
            if (artista == null)
                call.respond(HttpStatusCode.NotFound, "Artista no encontrado")
            else
                call.respond(artista)
        }
    }
}