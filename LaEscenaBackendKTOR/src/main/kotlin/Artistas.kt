package KtorLaEscena

import org.jetbrains.exposed.sql.Table

object Artistas : Table("artistas") {
    val id         = integer("id").autoIncrement()
    val nombre     = varchar("nombre", 255)
    val correo     = varchar("correo", 255)
    val contrasena = varchar("contraseña", 255)
    val telefono   = varchar("teléfono", 20)

    override val primaryKey = PrimaryKey(id)
}