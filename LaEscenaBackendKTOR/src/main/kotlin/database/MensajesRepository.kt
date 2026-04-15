package database

import KtorLaEscena.Mensaje
import KtorLaEscena.MensajeCreateRequest
import KtorLaEscena.Mensajes
import org.jetbrains.exposed.sql.SortOrder
import org.jetbrains.exposed.sql.insert
import org.jetbrains.exposed.sql.selectAll
import org.jetbrains.exposed.sql.transactions.transaction
import java.time.LocalDateTime

object MensajesRepository {
    fun getByArtista(artistaId: Int): List<Mensaje> = transaction {
        Mensajes.selectAll()
            .where { Mensajes.artista_id eq artistaId }
            .orderBy(Mensajes.created_at to SortOrder.DESC)
            .map {
                Mensaje(
                    id = it[Mensajes.id],
                    artista_id = it[Mensajes.artista_id],
                    remitente = it[Mensajes.remitente],
                    asunto = it[Mensajes.asunto],
                    mensaje = it[Mensajes.mensaje],
                    created_at = it[Mensajes.created_at]
                )
            }
    }

    fun crear(req: MensajeCreateRequest) = transaction {
        Mensajes.insert {
            it[artista_id] = req.artista_id
            it[remitente] = req.remitente
            it[asunto] = req.asunto
            it[mensaje] = req.mensaje
            it[created_at] = LocalDateTime.now().toString()
        }
    }
}
