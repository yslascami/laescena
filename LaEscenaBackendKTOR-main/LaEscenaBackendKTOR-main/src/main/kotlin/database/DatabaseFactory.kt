package KtorLaEscena.database


import org.jetbrains.exposed.sql.Database
import org.jetbrains.exposed.sql.SchemaUtils
import org.jetbrains.exposed.sql.transactions.transaction

import KtorLaEscena.Users
object DatabaseFactory {

    fun init() {

        Database.connect(
            url = "jdbc:mysql://localhost:3306/laescena?serverTimezone=UTC",
            driver = "com.mysql.cj.jdbc.Driver",
            user = "root",
            password = ""
        )
        transaction {
            SchemaUtils.create(Users)
        }

    }


}