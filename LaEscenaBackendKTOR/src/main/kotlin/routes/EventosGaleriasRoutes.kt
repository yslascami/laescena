package KtorLaEscena.routes

import database.EventosRepository
import database.GaleriasRepository
import io.ktor.server.application.*
import io.ktor.server.response.*
import io.ktor.server.routing.*

fun Route.eventosGaleriasRoutes() {
    get("/eventos") {
        try {
            val eventos = EventosRepository.getAll()
            call.respond(eventos)
        } catch (e: Exception) {
            call.respond(mapOf("error" to e.localizedMessage))
        }
    }

    get("/galerias") {
        try {
            val galerias = GaleriasRepository.getAll()
            call.respond(galerias)
        } catch (e: Exception) {
            call.respond(mapOf("error" to e.localizedMessage))
        }
    }
}
