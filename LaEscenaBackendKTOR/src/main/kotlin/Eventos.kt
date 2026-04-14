package KtorLaEscena

import org.jetbrains.exposed.sql.Table

object Eventos : Table("eventos") {
    val id = integer("id").autoIncrement()
    val nombre = varchar("nombre", 255)
    val descripcion = text("descripcion")
    val fecha = varchar("fecha", 100)
    val lugar = varchar("lugar", 255)
    val imagen_url = varchar("imagen_url", 255).nullable()

    override val primaryKey = PrimaryKey(id)
}
