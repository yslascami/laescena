package KtorLaEscena.database

import KtorLaEscena.Users
import org.jetbrains.exposed.sql.*
import org.jetbrains.exposed.sql.transactions.transaction

object UserRepository {
    fun login(email: String, password: String): String? {
        println("🔍 Login intento: email=$email, password=$password")
        return transaction {
            Users.selectAll().where {
                (Users.email eq email) and (Users.password eq password)
            }.also { rows ->
                println("🔍 Filas encontradas: ${rows.count()}")
            }.map { it[Users.role] }
                .singleOrNull()


        }
    }
}
