package database

import KtorLaEscena.Users
import org.jetbrains.exposed.sql.*
import org.jetbrains.exposed.sql.transactions.transaction

object UserRepository {
    fun login(email: String, password: String): Pair<Int, String>? {
        println("🔍 Login intento: email=$email, password=$password")
        return transaction {
            Users.selectAll().where {
                (Users.email eq email) and (Users.password eq password)
            }.map { 
                it[Users.id] to it[Users.role] 
            }.singleOrNull()
        }
    }
}
