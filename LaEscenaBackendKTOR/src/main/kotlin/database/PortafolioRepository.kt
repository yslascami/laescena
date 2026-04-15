package database

import KtorLaEscena.Portafolio
import KtorLaEscena.PortafolioItem
import KtorLaEscena.PortafolioCreateRequest
import org.jetbrains.exposed.sql.SqlExpressionBuilder.eq
import org.jetbrains.exposed.sql.deleteWhere
import org.jetbrains.exposed.sql.insert
import org.jetbrains.exposed.sql.selectAll
import org.jetbrains.exposed.sql.transactions.transaction
import java.time.LocalDateTime

object PortafolioRepository {
    fun guardarPortafolio(
        artistaId: Int,
        nombreArtista: String?,
        titulo: String,
        descripcion: String?,
        tipo: String,
        rutaArchivo: String
    ) {
        transaction {
            Portafolio.insert {
                it[Portafolio.artistaId] = artistaId
                it[Portafolio.nombre_artista] = nombreArtista
                it[Portafolio.titulo] = titulo
                it[Portafolio.descripcion] = descripcion
                it[Portafolio.tipo] = tipo
                it[Portafolio.archivo] = rutaArchivo
                it[Portafolio.nombre_original] = titulo
                it[Portafolio.created_at] = LocalDateTime.now().toString()
            }
        }
    }

    fun crearEntrada(req: PortafolioCreateRequest) = transaction {
        Portafolio.insert {
            it[Portafolio.artistaId] = req.artista_id
            it[Portafolio.nombre_artista] = req.nombre_artista
            it[Portafolio.tipo] = req.tipo
            it[Portafolio.archivo] = req.archivo
            it[Portafolio.titulo] = req.titulo
            it[Portafolio.descripcion] = req.descripcion
            it[Portafolio.nombre_original] = req.titulo
            it[Portafolio.created_at] = LocalDateTime.now().toString()
        }
    }

    fun getByArtista(artistaId: Int): List<PortafolioItem> = transaction {
        Portafolio.selectAll()
            .where { Portafolio.artistaId eq artistaId }
            .map {
                PortafolioItem(
                    id = it[Portafolio.id],
                    artista_id = it[Portafolio.artistaId],
                    nombre_artista = it[Portafolio.nombre_artista],
                    tipo = it[Portafolio.tipo],
                    archivo = it[Portafolio.archivo],
                    titulo = it[Portafolio.titulo],
                    descripcion = it[Portafolio.descripcion],
                    nombre_original = it[Portafolio.nombre_original],
                    created_at = it[Portafolio.created_at]
                )
            }
    }

    fun eliminar(id: Int) = transaction {
        Portafolio.deleteWhere { Portafolio.id eq id }
    }
}
