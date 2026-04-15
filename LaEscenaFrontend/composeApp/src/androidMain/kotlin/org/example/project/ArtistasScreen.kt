package org.example.project

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.unit.dp
import androidx.lifecycle.viewmodel.compose.viewModel

@Composable
fun ArtistasScreen(
    onArtistClick: (Int) -> Unit = {},
    viewModel: ArtistasViewModel = viewModel { ArtistasViewModel() }
) {
    val artistas by viewModel.artistas.collectAsState()
    val isLoading by viewModel.isLoading.collectAsState()
    val error by viewModel.error.collectAsState()

    LaunchedEffect(Unit) {
        viewModel.cargarArtistas()
    }

    Column(
        modifier = Modifier.fillMaxSize().background(ColorFondo).padding(16.dp)
    ) {
        Text(
            text = "Catálogo de Artistas",
            style = MaterialTheme.typography.headlineMedium,
            color = ColorPrimario
        )

        Spacer(modifier = Modifier.height(16.dp))

        when {
            isLoading -> {
                Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                    CircularProgressIndicator(color = ColorPrimario)
                }
            }
            error.isNotEmpty() -> {
                Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
                    Text(text = error, color = Color.Red)
                }
            }
            else -> {
                LazyColumn(verticalArrangement = Arrangement.spacedBy(12.dp)) {
                    items(artistas) { artista ->
                        Card(
                            modifier = Modifier.fillMaxWidth(),
                            onClick = { onArtistClick(artista.id) },
                            colors = CardDefaults.cardColors(containerColor = ColorSuperficie),
                            shape = RoundedCornerShape(12.dp)
                        ) {
                            Column(modifier = Modifier.padding(16.dp)) {
                                Text(text = artista.nombre, style = MaterialTheme.typography.titleMedium, color = ColorTexto)
                                Text(text = artista.correo, style = MaterialTheme.typography.bodyMedium, color = ColorTextoSecundario)
                            }
                        }
                    }
                }
            }
        }
    }
}
