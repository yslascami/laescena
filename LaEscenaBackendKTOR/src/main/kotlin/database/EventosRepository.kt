package database

import KtorLaEscena.Evento
import KtorLaEscena.Eventos
import org.jetbrains.exposed.sql.selectAll
import org.jetbrains.exposed.sql.transactions.transaction

object EventosRepository {
    fun getAll(): List<Evento> = transaction {
        Eventos.selectAll().map {
            Evento(
                id = it[Eventos.id],
                nombre = it[Eventos.nombre],
                descripcion = it[Eventos.descripcion],
                fecha = it[Eventos.fecha],
                lugar = it[Eventos.lugar],
                imagen_url = it[Eventos.imagen_url]
            )
        }
    }
}
