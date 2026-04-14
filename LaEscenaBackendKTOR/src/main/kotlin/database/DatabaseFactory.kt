package database

import KtorLaEscena.Artistas
import KtorLaEscena.Portafolio
import KtorLaEscena.Eventos
import KtorLaEscena.Galerias
import org.jetbrains.exposed.sql.Database
import org.jetbrains.exposed.sql.SchemaUtils
import org.jetbrains.exposed.sql.transactions.transaction
import KtorLaEscena.Users

object DatabaseFactory {
    fun init() {
        val dbHost     = System.getenv("DB_HOST")     ?: "localhost"
        val dbPort     = System.getenv("DB_PORT")     ?: "3306"
        val dbName     = System.getenv("DB_NAME")     ?: "laescena"
        val dbUser     = System.getenv("DB_USER")     ?: "root"
        val dbPassword = System.getenv("DB_PASSWORD") ?: ""

        Database.connect(
            url      = "jdbc:mysql://$dbHost:$dbPort/$dbName?useSSL=false&allowPublicKeyRetrieval=true",
            driver   = "com.mysql.cj.jdbc.Driver",
            user     = dbUser,
            password = dbPassword
        )
    }
}
