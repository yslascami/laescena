package KtorLaEscena

import database.DatabaseFactory
import KtorLaEscena.routes.*
import io.ktor.server.application.*
import io.ktor.server.routing.*
import io.ktor.server.plugins.contentnegotiation.*
import io.ktor.serialization.kotlinx.json.*
import io.ktor.server.http.content.*
import java.io.File

fun main(args: Array<String>) {
    io.ktor.server.netty.EngineMain.main(args)
}

fun Application.module() {
    DatabaseFactory.init()

    install(ContentNegotiation) {
        json()
    }

    routing {
        loginRoute()
        registerRoute()
        portfolioRoutes()
        eventosGaleriasRoutes()
        mensajesRoutes() // Nueva ruta de mensajería agregada
        
        staticFiles("/uploads", File("uploads"))
    }
    artistasRoutes()
}
