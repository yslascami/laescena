package org.example.project

import io.ktor.client.*
import io.ktor.client.call.*
import io.ktor.client.plugins.contentnegotiation.*
import io.ktor.client.request.*
import io.ktor.client.request.forms.*
import io.ktor.client.statement.*
import io.ktor.http.*
import io.ktor.serialization.kotlinx.json.*
import kotlinx.serialization.Serializable
import kotlinx.serialization.json.Json

@Serializable
data class LoginRequest(val email: String, val password: String)

@Serializable
data class RegisterRequest(val email: String, val password: String, val role: String)

@Serializable
data class CommonResponse(
    val success: Boolean,
    val message: String,
    val role: String? = null,
    val id: Int? = null
)

@Serializable
data class Artista(val id: Int, val nombre: String, val correo: String, val telefono: String)

@Serializable
data class Portafolio(
    val id: Int = 0,
    val artista_id: Int,
    val nombre_artista: String = "",
    val tipo: String,
    val archivo: String,
    val titulo: String,
    val descripcion: String,
    val nombre_original: String = "",
    val created_at: String = ""
)

@Serializable
data class Mensaje(
    val id: Int = 0,
    val artista_id: Int,
    val remitente: String,
    val asunto: String,
    val mensaje: String,
    val leido: Int = 0,
    val created_at: String = ""
)

@Serializable
data class Evento(
    val id: Int = 0,
    val titulo: String,
    val artista: String = "",
    val descripcion: String = "",
    val fecha: String,
    val hora: String = "",
    val lugar: String,
    val categoria: String = "",
    val imagen_url: String? = null
)

class Apiservice {
    private val client = HttpClient {
        install(ContentNegotiation) {
            json(Json {
                ignoreUnknownKeys = true
                useAlternativeNames = false
            })
        }
    }

    private val baseUrl = "https://laescena-production-5298.up.railway.app"
    val mediaUrl = "$baseUrl/uploads"

    suspend fun getMensajes(artistaId: Int): List<Mensaje> {
        return try {
            client.get("$baseUrl/mensajes/$artistaId").body()
        } catch (e: Exception) {
            emptyList()
        }
    }

    suspend fun enviarMensaje(mensaje: Mensaje): CommonResponse {
        return try {
            val response: HttpResponse = client.post("$baseUrl/mensajes") {
                contentType(ContentType.Application.Json)
                setBody(mensaje)
            }
            response.body<CommonResponse>()
        } catch (e: Exception) {
            CommonResponse(success = false, message = "Error de red al enviar")
        }
    }

    suspend fun getPortafolio(artistaId: Int): List<Portafolio> {
        return try {
            client.get("$baseUrl/portafolio/$artistaId").body()
        } catch (e: Exception) {
            emptyList()
        }
    }

    suspend fun crearPortafolioConArchivo(
        artistaId: Int,
        nombreArtista: String,
        titulo: String,
        descripcion: String,
        tipo: String,
        nombreArchivo: String,
        archivoBytes: ByteArray
    ): CommonResponse {
        return try {
            val response: HttpResponse = client.submitFormWithBinaryData(
                url = "$baseUrl/portafolio/upload",
                formData = formData {
                    append("artista_id", artistaId)
                    append("nombre_artista", nombreArtista)
                    append("titulo", titulo)
                    append("descripcion", descripcion)
                    append("tipo", tipo)
                    append("nombre_original", nombreArchivo)
                    append("archivo", archivoBytes, Headers.build {
                        append(HttpHeaders.ContentType, when(tipo.lowercase()) {
                            "imagen" -> "image/jpeg"
                            "video" -> "video/mp4"
                            "pdf" -> "application/pdf"
                            else -> "application/octet-stream"
                        })
                        append(HttpHeaders.ContentDisposition, "filename=\"$nombreArchivo\"")
                    })
                }
            )
            response.body<CommonResponse>()
        } catch (e: Exception) {
            CommonResponse(success = false, message = "Error al subir archivo: ${e.message}")
        }
    }

    suspend fun eliminarPortafolio(id: Int): CommonResponse {
        return try {
            val response: HttpResponse = client.delete("$baseUrl/portafolio/$id")
            response.body<CommonResponse>()
        } catch (e: Exception) {
            CommonResponse(success = false, message = "Error al eliminar")
        }
    }

    suspend fun getArtistas(): List<Artista> {
        return try { client.get("$baseUrl/artistas").body() } catch (e: Exception) { emptyList() }
    }

    suspend fun getArtista(id: Int): Artista? {
        return try { client.get("$baseUrl/artistas/$id").body() } catch (e: Exception) { null }
    }

    suspend fun loginUsuario(email: String, password: String): CommonResponse {
        return try {
            val response: HttpResponse = client.post("$baseUrl/login") {
                contentType(ContentType.Application.Json)
                setBody(LoginRequest(email, password))
            }
            response.body<CommonResponse>()
        } catch (e: Exception) {
            CommonResponse(success = false, message = "Error de conexión")
        }
    }

    suspend fun registrarUsuario(email: String, password: String, role: String): CommonResponse {
        return try {
            val response: HttpResponse = client.post("$baseUrl/register") {
                contentType(ContentType.Application.Json)
                setBody(RegisterRequest(email, password, role))
            }
            val contentType = response.contentType()
            if (response.status == HttpStatusCode.OK || response.status == HttpStatusCode.Created) {
                if (contentType?.match(ContentType.Application.Json) == true) {
                    response.body<CommonResponse>()
                } else {
                    CommonResponse(success = true, message = response.bodyAsText())
                }
            } else {
                CommonResponse(success = false, message = "Error: ${response.status.description}")
            }
        } catch (e: Exception) {
            CommonResponse(success = false, message = "Error de conexión")
        }
    }
}