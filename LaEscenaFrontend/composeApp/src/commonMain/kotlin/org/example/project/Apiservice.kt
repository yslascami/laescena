package org.example.project

import io.ktor.client.*
import io.ktor.client.call.*
import io.ktor.client.plugins.contentnegotiation.*
import io.ktor.client.request.*
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
    val nombre_artista: String = "", // Nuevo campo solicitado
    val tipo: String,
    val archivo: String,
    val titulo: String,
    val descripcion: String,
    val nombre_original: String = "",
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

    private val baseUrl = "http://10.222.169.1:8080"

    suspend fun getPortafolio(artistaId: Int): List<Portafolio> {
        return try {
            client.get("$baseUrl/portafolio/$artistaId").body()
        } catch (e: Exception) {
            emptyList()
        }
    }

    suspend fun crearPortafolio(portafolio: Portafolio): CommonResponse {
        return try {
            val response: HttpResponse = client.post("$baseUrl/portafolio") {
                contentType(ContentType.Application.Json)
                setBody(portafolio)
            }
            response.body<CommonResponse>()
        } catch (e: Exception) {
            CommonResponse(success = false, message = "Error al conectar con el servidor")
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
            response.body<CommonResponse>()
        } catch (e: Exception) {
            CommonResponse(success = false, message = "Error de conexión")
        }
    }
}
