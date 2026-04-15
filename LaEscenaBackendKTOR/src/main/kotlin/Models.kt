package KtorLaEscena

import kotlinx.serialization.Serializable

@Serializable
data class LoginRequest(
    val email: String,
    val password: String
)

@Serializable
data class LoginResponse(
    val success: Boolean,
    val message: String,
    val id: Int? = null,
    val role: String? = null
)

@Serializable
data class RegisterRequest(
    val email: String,
    val password: String,
    val role: String
)

@Serializable
data class Evento(
    val id: Int,
    val nombre: String,
    val descripcion: String,
    val fecha: String,
    val lugar: String,
    val imagen_url: String? = null
)

@Serializable
data class Galeria(
    val id: Int,
    val nombre: String,
    val artista_id: Int,
    val imagen_url: String
)

@Serializable
data class PortafolioItem(
    val id: Int,
    val artista_id: Int,
    val nombre_artista: String?,
    val tipo: String,
    val archivo: String,
    val titulo: String,
    val descripcion: String?,
    val nombre_original: String?,
    val created_at: String
)

@Serializable
data class PortafolioCreateRequest(
    val artista_id: Int,
    val nombre_artista: String? = null,
    val tipo: String,
    val archivo: String,
    val titulo: String,
    val descripcion: String? = null
)

@Serializable
data class Mensaje(
    val id: Int,
    val artista_id: Int,
    val remitente: String,
    val asunto: String,
    val mensaje: String,
    val created_at: String // Cambiado de 'fecha' a 'created_at'
)

@Serializable
data class MensajeCreateRequest(
    val artista_id: Int,
    val remitente: String,
    val asunto: String,
    val mensaje: String
)
