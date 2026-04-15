package database

import KtorLaEscena.*
import org.jetbrains.exposed.sql.Database
import org.jetbrains.exposed.sql.SchemaUtils
import org.jetbrains.exposed.sql.transactions.transaction

object DatabaseFactory {
    fun init() {
        val host = System.getenv("DB_HOST") ?: "localhost"
        val port = System.getenv("DB_PORT") ?: "3306"
        val name = System.getenv("DB_NAME") ?: "laescena"
        val user = System.getenv("DB_USER") ?: "root"
        val password = System.getenv("DB_PASSWORD") ?: ""
        println("Conectando a: jdbc:mysql://$host:$port/$name")  // <-- agrega esto
        println("Usuario: $user")

        Database.connect(
            url = "jdbc:mysql://$host:$port/$name?serverTimezone=UTC",
            driver = "com.mysql.cj.jdbc.Driver",
            user = user,
            password = password
        )
        transaction {
            SchemaUtils.create(Users, Artistas, Portafolio, Eventos, Galerias, Mensajes)
        }
    }
}
