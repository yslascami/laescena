package KtorLaEscena
import KtorLaEscena.database.DatabaseFactory


import io.ktor.server.application.*
import io.ktor.server.routing.routing
import io.ktor.server.plugins.contentnegotiation.*
import io.ktor.serialization.kotlinx.json.*

import KtorLaEscena.routes.registerRoute


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
    }
}
