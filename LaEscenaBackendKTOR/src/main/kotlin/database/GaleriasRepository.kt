package database

import KtorLaEscena.Galeria
import KtorLaEscena.Galerias
import org.jetbrains.exposed.sql.selectAll
import org.jetbrains.exposed.sql.transactions.transaction

object GaleriasRepository {
    fun getAll(): List<Galeria> = transaction {
        Galerias.selectAll().map {
            Galeria(
                id = it[Galerias.id],
                nombre = it[Galerias.nombre],
                artista_id = it[Galerias.artista_id],
                imagen_url = it[Galerias.imagen_url]
            )
        }
    }
}
