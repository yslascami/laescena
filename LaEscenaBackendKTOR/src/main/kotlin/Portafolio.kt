package KtorLaEscena

import org.jetbrains.exposed.sql.Table

object Portafolio : Table("portafolio") {
    val id = integer("id").autoIncrement()
    val artistaId = integer("artista_id").references(Artistas.id)
    val tipoArchivo = varchar("tipo_archivo", 20)
    val url = varchar("url", 255)
    val titulo = varchar("titulo", 100)

    override val primaryKey = PrimaryKey(id)
}
