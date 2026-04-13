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
data class LoginRequest(
    val email: String,
    val password: String
)

@Serializable
data class RegisterRequest(
    val email: String,
    val password: String,
    val role: String
)

@Serializable
data class CommonResponse(
    val success: Boolean,
    val message: String,
    val role: String? = null
)

@Serializable
data class Artista(
    val id: Int,
    val nombre: String,
    val correo: String,
    val telefono: String
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

    private val baseUrl = "http://10.0.2.2:8080" 

    suspend fun loginUsuario(email: String, password: String): CommonResponse {
        return try {
            val response: HttpResponse = client.post("$baseUrl/login") {
                contentType(ContentType.Application.Json)
                setBody(LoginRequest(email, password))
            }
            
            val contentType = response.contentType()
            if (response.status == HttpStatusCode.OK) {
                if (contentType?.match(ContentType.Application.Json) == true) {
                    response.body<CommonResponse>()
                } else {
                    CommonResponse(success = true, message = response.bodyAsText())
                }
            } else {
                CommonResponse(success = false, message = "Error: ${response.status.description}")
            }
        } catch (e: Exception) {
            CommonResponse(success = false, message = "Error de conexión: ${e.message ?: "Error desconocido"}")
        }
    }
    suspend fun getArtistas(): List<Artista> {
        return try {
            client.get("$baseUrl/artistas").body()
        } catch (e: Exception) {
            emptyList()
        }
    }

    suspend fun getArtista(id: Int): Artista? {
        return try {
            client.get("$baseUrl/artistas/$id").body()
        } catch (e: Exception) {
            null
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
                    // Si el servidor devuelve texto plano (como 201 Created con texto)
                    CommonResponse(success = true, message = response.bodyAsText())
                }
            } else {
                CommonResponse(success = false, message = "Error: ${response.status.description}")
            }
        } catch (e: Exception) {
            CommonResponse(success = false, message = "Error de conexión: ${e.message ?: "Error desconocido"}")
        }
    }
}
