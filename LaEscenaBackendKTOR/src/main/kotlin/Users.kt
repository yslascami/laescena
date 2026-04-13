package KtorLaEscena

import org.jetbrains.exposed.sql.Table

object Users : Table() {
    val id = integer("id").autoIncrement()
    val email = varchar("email", 100).uniqueIndex()   // correo único
    val password = varchar("password", 100)           // contraseña
    val role = varchar("role", 50)                    // perfil: "superadmin", "artista", "centrocultural"
    override val primaryKey = PrimaryKey(id)
}