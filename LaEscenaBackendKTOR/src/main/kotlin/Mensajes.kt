package KtorLaEscena

import org.jetbrains.exposed.sql.Table

object Mensajes : Table("mensajes") {
    val id = integer("id").autoIncrement()
    val artista_id = integer("artista_id").references(Artistas.id)
    val remitente = varchar("remitente", 255)
    val asunto = varchar("asunto", 255)
    val mensaje = text("mensaje")
    val created_at = varchar("created_at", 50) // Cambiado de 'fecha' a 'created_at' para coincidir con MySQL

    override val primaryKey = PrimaryKey(id)
}
