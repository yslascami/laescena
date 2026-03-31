package KtorLaEscena.database

import KtorLaEscena.Users

import org.jetbrains.exposed.sql.*
import org.jetbrains.exposed.sql.select
import org.jetbrains.exposed.sql.transactions.transaction

object UserRepository {
    fun login(email: String, password: String): String? {
        return transaction {
            Users.select {
                (Users.email eq email) and (Users.password eq password)
            }.map { it[Users.role] }
                .singleOrNull()
        }
    }
}
