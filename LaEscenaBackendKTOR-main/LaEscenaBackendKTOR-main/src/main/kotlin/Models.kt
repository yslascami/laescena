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
    val email: String,
    val password: String,
    val role: String
)