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
    val role: String? = null
)

@Serializable
data class RegisterRequest(
    val nombre: String,
    val email: String,
    val password: String,
    val role: String= "artista"
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
