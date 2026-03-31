package KtorLaEscena.routes   // o KtorLaEscena.models si prefieres separar

import kotlinx.serialization.Serializable

@Serializable
data class LoginResponse(
    val id: Int,
    val email: String,
    val role: String
)
