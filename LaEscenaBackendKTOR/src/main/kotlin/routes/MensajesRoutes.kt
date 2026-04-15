package KtorLaEscena.routes

import KtorLaEscena.MensajeCreateRequest
import database.MensajesRepository
import io.ktor.http.*
import io.ktor.server.application.*
import io.ktor.server.request.*
import io.ktor.server.response.*
import io.ktor.server.routing.*

fun Route.mensajesRoutes() {
    // 1. GET /mensajes/{artistaId}
    get("/mensajes/{artistaId}") {
        val artistaId = call.parameters["artistaId"]?.toIntOrNull()
        if (artistaId == null) {
            call.respond(HttpStatusCode.BadRequest, "ID de artista inválido")
            return@get
        }

        try {
            val mensajes = MensajesRepository.getByArtista(artistaId)
            call.respond(mensajes)
        } catch (e: Exception) {
            call.respond(HttpStatusCode.InternalServerError, "Error al obtener mensajes: ${e.localizedMessage}")
        }
    }

    // 2. POST /mensajes
    post("/mensajes") {
        try {
            val request = call.receive<MensajeCreateRequest>()
            MensajesRepository.crear(request)
            call.respond(HttpStatusCode.Created, mapOf("message" to "Mensaje enviado con éxito"))
        } catch (e: Exception) {
            call.respond(HttpStatusCode.BadRequest, mapOf("message" to "Error al enviar mensaje: ${e.localizedMessage}"))
        }
    }
}
