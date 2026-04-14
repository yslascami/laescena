package org.example.project

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import androidx.lifecycle.viewmodel.compose.viewModel

@Composable
fun ArtistasScreen(
    onArtistClick: (String) -> Unit = {},
    viewModel: ArtistasViewModel = viewModel { ArtistasViewModel() }
) {
    val artistas by viewModel.artistas.collectAsState()
    val isLoading by viewModel.isLoading.collectAsState()
    val error by viewModel.error.collectAsState()

    LaunchedEffect(Unit) {
        viewModel.cargarArtistas()
    }

    Column(
        modifier = Modifier.fillMaxSize().padding(16.dp)
    ) {
        Text(
            text = "Artistas",
            style = MaterialTheme.typography.headlineMedium
        )

        Spacer(modifier = Modifier.height(16.dp))

        when {
            isLoading -> {
                CircularProgressIndicator(
                    modifier = Modifier.align(Alignment.CenterHorizontally)
                )
            }
            error.isNotEmpty() -> {
                Text(text = error, color = MaterialTheme.colorScheme.error)
            }
            else -> {
                LazyColumn {
                    items(artistas) { artista ->
                        Card(
                            modifier = Modifier
                                .fillMaxWidth()
                                .padding(vertical = 4.dp),
                            onClick = { onArtistClick(artista.nombre) }
                        ) {
                            Column(modifier = Modifier.padding(16.dp)) {
                                Text(text = artista.nombre, style = MaterialTheme.typography.titleMedium)
                                Text(text = artista.correo, style = MaterialTheme.typography.bodyMedium)
                                Text(text = artista.telefono, style = MaterialTheme.typography.bodySmall)
                            }
                        }
                    }
                }
            }
        }
    }
}