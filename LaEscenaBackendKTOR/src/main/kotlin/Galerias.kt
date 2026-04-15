package KtorLaEscena

import org.jetbrains.exposed.sql.Table

object Galerias : Table("galerias") {
    val id = integer("id").autoIncrement()
    val nombre = varchar("nombre", 255)
    val artista_id = integer("artista_id").references(Artistas.id)
    val imagen_url = varchar("imagen_url", 255)

    override val primaryKey = PrimaryKey(id)
}
