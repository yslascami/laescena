package KtorLaEscena.database

import KtorLaEscena.Artistas
import kotlinx.serialization.Serializable
import org.jetbrains.exposed.sql.*
import org.jetbrains.exposed.sql.transactions.transaction

@Serializable
data class ArtistaDTO(
    val id: Int,
    val nombre: String,
    val correo: String,
    val telefono: String
    // ⚠️ No exponemos contraseña
)

object ArtistaRepository {

    fun getAll(): List<ArtistaDTO> = transaction {
        Artistas.selectAll().map {
            ArtistaDTO(
                id       = it[Artistas.id],
                nombre   = it[Artistas.nombre],
                correo   = it[Artistas.correo],
                telefono = it[Artistas.telefono]
            )
        }
    }

    fun getById(artistaId: Int): ArtistaDTO? = transaction {
        Artistas.selectAll()
            .where { Artistas.id eq artistaId }
            .map {
                ArtistaDTO(
                    id       = it[Artistas.id],
                    nombre   = it[Artistas.nombre],
                    correo   = it[Artistas.correo],
                    telefono = it[Artistas.telefono]
                )
            }.singleOrNull()
    }
}