package KtorLaEscena.routes

import database.PortafolioRepository
import io.ktor.http.*
import io.ktor.http.content.*
import io.ktor.server.application.*
import io.ktor.server.request.*
import io.ktor.server.response.*
import io.ktor.server.routing.*
import java.io.File
import java.util.*

fun Route.portfolioRoutes() {
    // 1. Subir archivos (Multipart)
    post("/portafolio/upload") {
        val multipart = call.receiveMultipart()
        var artistaId = 0
        var nombreArtista = ""
        var titulo = ""
        var descripcion = ""
        var tipo = ""
        var fileName = ""

        multipart.forEachPart { part ->
            when (part) {
                is PartData.FormItem -> {
                    when (part.name) {
                        "artista_id" -> artistaId = part.value.toIntOrNull() ?: 0
                        "nombre_artista" -> nombreArtista = part.value
                        "titulo" -> titulo = part.value
                        "descripcion" -> descripcion = part.value
                        "tipo" -> tipo = part.value
                    }
                }
                is PartData.FileItem -> {
                    if (part.name == "archivo") {
                        val originalName = part.originalFileName ?: "archivo"
                        val ext = File(originalName).extension
                        fileName = "port_${artistaId}_${System.currentTimeMillis()}.$ext"
                        
                        val dir = File("uploads")
                        if (!dir.exists()) dir.mkdirs()
                        
                        val file = File(dir, fileName)
                        part.streamProvider().use { input ->
                            file.outputStream().buffered().use { output ->
                                input.copyTo(output)
                            }
                        }
                    }
                }
                else -> {}
            }
            part.dispose()
        }

        try {
            PortafolioRepository.guardarPortafolio(
                artistaId = artistaId,
                nombreArtista = nombreArtista,
                titulo = titulo,
                descripcion = descripcion,
                tipo = tipo,
                rutaArchivo = "uploads/$fileName"
            )
            call.respond(HttpStatusCode.Created, mapOf(
                "message" to "Portafolio subido con éxito", 
                "url" to "uploads/$fileName"
            ))
        } catch (e: Exception) {
            call.respond(HttpStatusCode.InternalServerError, "Error al guardar en BD: ${e.message}")
        }
    }

    // 2. Obtener Portafolio por Artista
    get("/portafolio/{artistaId}") {
        val artistaId = call.parameters["artistaId"]?.toIntOrNull()
        if (artistaId == null) {
            call.respond(HttpStatusCode.BadRequest, "ID de artista inválido")
            return@get
        }
        try {
            val items = PortafolioRepository.getByArtista(artistaId)
            call.respond(items)
        } catch (e: Exception) {
            call.respond(HttpStatusCode.InternalServerError, "Error al obtener: ${e.localizedMessage}")
        }
    }

    // 3. NUEVO: Eliminar registro por ID
    delete("/portafolio/{id}") {
        val id = call.parameters["id"]?.toIntOrNull()
        if (id == null) {
            call.respond(HttpStatusCode.BadRequest, "ID inválido")
            return@delete
        }

        try {
            val filasEliminadas = PortafolioRepository.eliminar(id)
            if (filasEliminadas > 0) {
                call.respond(HttpStatusCode.OK, mapOf("message" to "Registro eliminado correctamente"))
            } else {
                call.respond(HttpStatusCode.NotFound, mapOf("message" to "No se encontró el registro con ese ID"))
            }
        } catch (e: Exception) {
            call.respond(HttpStatusCode.InternalServerError, mapOf("message" to "Error al eliminar: ${e.localizedMessage}"))
        }
    }
}
