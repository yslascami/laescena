package KtorLaEscena

import org.jetbrains.exposed.sql.Table

object Portafolio : Table("portafolio") {
    val id = integer("id").autoIncrement()
    val artistaId = integer("artista_id").references(Artistas.id)
    val nombre_artista = varchar("nombre_artista", 255).nullable()
    val tipo = varchar("tipo", 20)
    val archivo = varchar("archivo", 255)
    val titulo = varchar("titulo", 100)
    val descripcion = text("descripcion").nullable()
    val nombre_original = varchar("nombre_original", 255).nullable()
    val created_at = varchar("created_at", 50)

    override val primaryKey = PrimaryKey(id)
}
