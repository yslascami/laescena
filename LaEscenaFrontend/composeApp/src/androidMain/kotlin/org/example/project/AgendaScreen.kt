package org.example.project

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.CalendarToday
import androidx.compose.material.icons.filled.LocationOn
import androidx.compose.material.icons.filled.Schedule
import androidx.compose.material3.*
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp

// NOTA: Se eliminó 'data class Evento' de aquí porque ya está definida en Apiservice.kt

val eventosEjemplo = listOf(
    Evento(titulo = "Exposición de Arte Contemporáneo", artista = "María González", fecha = "15 Mar 2026", hora = "18:00", lugar = "Centro Cultural", categoria = "Pintura"),
    Evento(titulo = "Performance en Vivo", artista = "Carlos Ruiz", fecha = "20 Mar 2026", hora = "20:00", lugar = "Centro Cultural", categoria = "Performance"),
    Evento(titulo = "Instalación Interactiva", artista = "Ana Martínez", fecha = "25 Mar 2026", hora = "17:00", lugar = "Centro Cultural", categoria = "Instalación"),
    Evento(titulo = "Concierto de Jazz", artista = "Luis Torres", fecha = "28 Mar 2026", hora = "19:00", lugar = "Centro Cultural", categoria = "Música"),
)

@Composable
fun AgendaScreen(onBack: () -> Unit = {}) {
    Column(
        modifier = Modifier
            .fillMaxSize()
            .background(ColorFondo)
    ) {
        // Header
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .padding(horizontal = 16.dp, vertical = 12.dp),
            horizontalArrangement = Arrangement.SpaceBetween,
            verticalAlignment = Alignment.CenterVertically
        ) {
            Text(
                text = "Agenda Cultural",
                fontSize = 22.sp,
                fontWeight = FontWeight.Bold,
                color = ColorTexto
            )
            IconButton(onClick = onBack) {
                Icon(
                    imageVector = Icons.Default.CalendarToday,
                    contentDescription = "Cerrar",
                    tint = ColorTexto
                )
            }
        }

        LazyColumn(
            modifier = Modifier.fillMaxSize(),
            contentPadding = PaddingValues(16.dp),
            verticalArrangement = Arrangement.spacedBy(12.dp)
        ) {
            items(eventosEjemplo) { evento ->
                Card(
                    modifier = Modifier.fillMaxWidth(),
                    shape = RoundedCornerShape(16.dp),
                    colors = CardDefaults.cardColors(containerColor = ColorSuperficie)
                ) {
                    Column(modifier = Modifier.padding(16.dp)) {
                        // Título y badge
                        Row(
                            modifier = Modifier.fillMaxWidth(),
                            horizontalArrangement = Arrangement.SpaceBetween,
                            verticalAlignment = Alignment.Top
                        ) {
                            Text(
                                text = evento.titulo,
                                fontSize = 16.sp,
                                fontWeight = FontWeight.Bold,
                                color = ColorTexto,
                                modifier = Modifier.weight(1f)
                            )
                            Spacer(modifier = Modifier.width(8.dp))
                            Box(
                                modifier = Modifier
                                    .background(ColorPrimario, RoundedCornerShape(20.dp))
                                    .padding(horizontal = 10.dp, vertical = 4.dp)
                            ) {
                                Text(evento.categoria, color = Color.White, fontSize = 11.sp)
                            }
                        }

                        Spacer(modifier = Modifier.height(4.dp))

                        // Artista
                        Text(
                            text = "por ${evento.artista}",
                            fontSize = 13.sp,
                            color = ColorTextoSecundario
                        )

                        Spacer(modifier = Modifier.height(12.dp))

                        // Fecha
                        Row(verticalAlignment = Alignment.CenterVertically) {
                            Icon(Icons.Default.CalendarToday, contentDescription = null, tint = ColorPrimario, modifier = Modifier.size(14.dp))
                            Spacer(modifier = Modifier.width(6.dp))
                            Text(evento.fecha, fontSize = 13.sp, color = ColorTextoSecundario)
                        }

                        Spacer(modifier = Modifier.height(4.dp))

                        // Hora
                        Row(verticalAlignment = Alignment.CenterVertically) {
                            Icon(Icons.Default.Schedule, contentDescription = null, tint = ColorPrimario, modifier = Modifier.size(14.dp))
                            Spacer(modifier = Modifier.width(6.dp))
                            Text(evento.hora, fontSize = 13.sp, color = ColorTextoSecundario)
                        }

                        Spacer(modifier = Modifier.height(4.dp))

                        // Lugar
                        Row(verticalAlignment = Alignment.CenterVertically) {
                            Icon(Icons.Default.LocationOn, contentDescription = null, tint = ColorPrimario, modifier = Modifier.size(14.dp))
                            Spacer(modifier = Modifier.width(6.dp))
                            Text(evento.lugar, fontSize = 13.sp, color = ColorTextoSecundario)
                        }

                        Spacer(modifier = Modifier.height(16.dp))

                        // Botón
                        Button(
                            onClick = {},
                            modifier = Modifier.fillMaxWidth(),
                            shape = RoundedCornerShape(8.dp),
                            colors = ButtonDefaults.buttonColors(containerColor = ColorPrimario)
                        ) {
                            Text("Más información", color = Color.White)
                        }
                    }
                }
            }
        }
    }
}
