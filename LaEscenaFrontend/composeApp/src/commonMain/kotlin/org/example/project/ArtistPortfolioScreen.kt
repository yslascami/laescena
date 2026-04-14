package org.example.project

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.filled.Add
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.NavController

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun ArtistPortfolioScreen(navController: NavController, artistaId: Int) {
    val portafolioViewModel: PortafolioViewModel = viewModel { PortafolioViewModel() }
    val artistasViewModel: ArtistasViewModel = viewModel { ArtistasViewModel() }
    
    val items by portafolioViewModel.portafolioItems.collectAsState()
    val artista by artistasViewModel.artistaSeleccionado.collectAsState()
    val isLoading by portafolioViewModel.isLoading.collectAsState()
    val error by portafolioViewModel.error.collectAsState()

    LaunchedEffect(artistaId) {
        portafolioViewModel.cargarPortafolio(artistaId)
        artistasViewModel.cargarArtista(artistaId)
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { 
                    Column {
                        Text("Portafolio", color = ColorPrimario, fontSize = 18.sp, fontWeight = FontWeight.Bold)
                        artista?.let { 
                            Text("de ${it.nombre}", color = ColorTexto, fontSize = 12.sp) 
                        }
                    }
                },
                navigationIcon = {
                    IconButton(onClick = { navController.popBackStack() }) {
                        Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = "Atrás", tint = ColorPrimario)
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(containerColor = ColorFondo)
            )
        },
        floatingActionButton = {
            FloatingActionButton(
                onClick = { navController.navigate("artist_portfolio_form") },
                containerColor = ColorPrimario,
                contentColor = Color.White
            ) {
                Icon(Icons.Default.Add, contentDescription = "Crear Portafolio")
            }
        },
        containerColor = ColorFondo
    ) { padding ->
        Box(modifier = Modifier.fillMaxSize().padding(padding)) {
            if (isLoading) {
                CircularProgressIndicator(modifier = Modifier.align(Alignment.Center), color = ColorPrimario)
            } else if (error.isNotEmpty() && items.isEmpty()) {
                Text(
                    text = "Aún no tienes obras en tu portafolio.", 
                    color = ColorTextoSecundario, 
                    modifier = Modifier.align(Alignment.Center).padding(32.dp),
                    textAlign = TextAlign.Center
                )
            } else {
                LazyColumn(
                    modifier = Modifier.fillMaxSize().padding(16.dp),
                    verticalArrangement = Arrangement.spacedBy(16.dp)
                ) {
                    item {
                        artista?.let { 
                            Card(
                                modifier = Modifier.fillMaxWidth(),
                                colors = CardDefaults.cardColors(containerColor = ColorPrimario.copy(alpha = 0.1f))
                            ) {
                                Column(modifier = Modifier.padding(16.dp)) {
                                    Text("Información de Contacto", fontWeight = FontWeight.Bold, color = ColorPrimario)
                                    Text("Email: ${it.correo}", color = ColorTexto)
                                    Text("Tel: ${it.telefono}", color = ColorTexto)
                                }
                            }
                        }
                    }

                    items(items) { item ->
                        Card(
                            modifier = Modifier.fillMaxWidth(),
                            shape = RoundedCornerShape(16.dp),
                            colors = CardDefaults.cardColors(containerColor = ColorSuperficie)
                        ) {
                            Column(modifier = Modifier.padding(16.dp)) {
                                Box(
                                    modifier = Modifier
                                        .fillMaxWidth()
                                        .height(180.dp)
                                        .background(Color.Black.copy(alpha = 0.3f), RoundedCornerShape(8.dp)),
                                    contentAlignment = Alignment.Center
                                ) {
                                    Text("Imagen: ${item.titulo}", color = ColorTextoSecundario)
                                }
                                
                                Spacer(modifier = Modifier.height(12.dp))
                                
                                Text(text = item.titulo, fontSize = 18.sp, fontWeight = FontWeight.Bold, color = ColorTexto)
                                Text(text = item.descripcion, fontSize = 14.sp, color = ColorTextoSecundario)
                                
                                Spacer(modifier = Modifier.height(8.dp))
                                Row(verticalAlignment = Alignment.CenterVertically) {
                                    Badge(containerColor = ColorPrimario) { Text(item.tipo, color = Color.White) }
                                    Spacer(modifier = Modifier.width(8.dp))
                                    Text(text = "Subido el: ${item.created_at.split(" ")[0]}", fontSize = 11.sp, color = ColorTextoSecundario)
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
