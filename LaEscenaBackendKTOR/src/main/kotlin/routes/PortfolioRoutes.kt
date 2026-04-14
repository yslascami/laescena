package KtorLaEscena.routes

import database.PortafolioRepository
import io.ktor.http.content.*
import io.ktor.server.application.*
import io.ktor.server.request.*
import io.ktor.server.response.*
import io.ktor.server.routing.*
import java.io.File
import java.util.*

fun Route.portfolioRoutes() {
    post("/upload/{artistaId}") {
        val artistaId = call.parameters["artistaId"]?.toIntOrNull() ?: return@post call.respond("ID inválido")
        val multipart = call.receiveMultipart()
        var fileName = ""
        var tipo = ""

        multipart.forEachPart { part ->
            if (part is PartData.FileItem) {
                val originalName = part.originalFileName ?: "archivo"
                val ext = File(originalName).extension
                fileName = "${UUID.randomUUID()}.$ext"

                tipo = when (ext.lowercase()) {
                    "jpg", "png", "jpeg" -> "IMAGEN"
                    "mp4", "mov" -> "VIDEO"
                    "pdf" -> "PDF"
                    else -> "OTRO"
                }

                val uploadsDir = File("uploads")
                if (!uploadsDir.exists()) uploadsDir.mkdirs()

                val file = File(uploadsDir, fileName)
                part.streamProvider().use { input ->
                    file.outputStream().buffered().use { output ->
                        input.copyTo(output)
                    }
                }

                // Guardar en la base de datos
                PortafolioRepository.agregarArchivo(
                    artistaId = artistaId,
                    url = "/uploads/$fileName",
                    tipo = tipo,
                    titulo = originalName
                )
            }
            part.dispose()
        }

        call.respond(mapOf(
            "status" to "subido", 
            "url" to "/uploads/$fileName", 
            "tipo" to tipo
        ))
    }
}
