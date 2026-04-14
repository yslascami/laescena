package database

import KtorLaEscena.Portafolio
import org.jetbrains.exposed.sql.insert
import org.jetbrains.exposed.sql.transactions.transaction

object PortafolioRepository {
    fun agregarArchivo(artistaId: Int, url: String, tipo: String, titulo: String) {
        transaction {
            Portafolio.insert {
                it[Portafolio.artistaId] = artistaId
                it[Portafolio.url] = url
                it[Portafolio.tipoArchivo] = tipo
                it[Portafolio.titulo] = titulo
            }
        }
    }
}
