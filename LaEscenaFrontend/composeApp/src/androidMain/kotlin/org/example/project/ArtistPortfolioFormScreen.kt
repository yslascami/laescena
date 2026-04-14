package org.example.project

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.filled.*
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.NavController

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun ArtistPortfolioFormScreen(navController: NavController, userId: Int) {
    val portafolioViewModel: PortafolioViewModel = viewModel { PortafolioViewModel() }
    val artistasViewModel: ArtistasViewModel = viewModel { ArtistasViewModel() }
    
    var titulo by remember { mutableStateOf("") }
    var descripcion by remember { mutableStateOf("") }
    var tipo by remember { mutableStateOf("Imagen") }
    var archivoSimulado by remember { mutableStateOf("") }
    
    val isLoading by portafolioViewModel.isLoading.collectAsState()
    val uploadSuccess by portafolioViewModel.uploadSuccess.collectAsState()
    val error by portafolioViewModel.error.collectAsState()
    val artista by artistasViewModel.artistaSeleccionado.collectAsState()

    LaunchedEffect(userId) {
        artistasViewModel.cargarArtista(userId)
    }

    LaunchedEffect(uploadSuccess) {
        if (uploadSuccess) {
            navController.popBackStack()
            portafolioViewModel.resetUploadSuccess()
        }
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Crear Portafolio", color = ColorPrimario, fontWeight = FontWeight.Bold) },
                navigationIcon = {
                    IconButton(onClick = { navController.popBackStack() }) {
                        Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = "Atrás", tint = ColorPrimario)
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(containerColor = ColorFondo)
            )
        },
        containerColor = ColorFondo
    ) { padding ->
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(padding)
                .padding(16.dp)
                .verticalScroll(rememberScrollState()),
            verticalArrangement = Arrangement.spacedBy(16.dp)
        ) {
            artista?.let {
                Text(
                    text = "Autor: ${it.nombre}",
                    color = ColorTextoSecundario,
                    fontSize = 14.sp
                )
            }

            OutlinedTextField(
                value = titulo,
                onValueChange = { titulo = it },
                label = { Text("Nombre del Portafolio / Disciplina") },
                modifier = Modifier.fillMaxWidth(),
                colors = OutlinedTextFieldDefaults.colors(focusedTextColor = ColorTexto, unfocusedTextColor = ColorTexto)
            )

            OutlinedTextField(
                value = descripcion,
                onValueChange = { descripcion = it },
                label = { Text("Breve descripción") },
                modifier = Modifier.fillMaxWidth().height(100.dp),
                colors = OutlinedTextFieldDefaults.colors(focusedTextColor = ColorTexto, unfocusedTextColor = ColorTexto)
            )

            Text("Seleccionar tipo de contenido", color = ColorTexto, fontSize = 14.sp)
            Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                listOf("Imagen", "Video", "PDF").forEach { option ->
                    FilterChip(
                        selected = tipo == option,
                        onClick = { tipo = option },
                        label = { Text(option) },
                        colors = FilterChipDefaults.filterChipColors(labelColor = ColorTexto, selectedContainerColor = ColorPrimario)
                    )
                }
            }

            Card(
                modifier = Modifier.fillMaxWidth(),
                colors = CardDefaults.cardColors(containerColor = ColorSuperficie),
                shape = RoundedCornerShape(12.dp)
            ) {
                Column(modifier = Modifier.padding(16.dp), horizontalAlignment = Alignment.CenterHorizontally) {
                    Icon(Icons.Default.CloudUpload, contentDescription = null, tint = ColorPrimario, modifier = Modifier.size(40.dp))
                    Button(
                        onClick = { archivoSimulado = "portafolio/user_${userId}_${tipo.lowercase()}.file" },
                        colors = ButtonDefaults.buttonColors(containerColor = ColorPrimario)
                    ) {
                        Text("Subir $tipo", color = Color.White)
                    }
                    if (archivoSimulado.isNotEmpty()) {
                        Text("Archivo: $archivoSimulado", color = Color.Green, fontSize = 11.sp)
                    }
                }
            }

            if (error.isNotEmpty()) {
                Text(error, color = Color.Red, fontSize = 12.sp)
            }

            Button(
                onClick = { 
                    if (titulo.isNotBlank() && archivoSimulado.isNotEmpty()) {
                        portafolioViewModel.crearNuevoPortafolio(
                            artistaId = userId,
                            nombreArtista = artista?.nombre ?: "Artista Desconocido",
                            titulo = titulo,
                            descripcion = descripcion,
                            tipo = tipo,
                            archivo = archivoSimulado
                        )
                    }
                },
                modifier = Modifier.fillMaxWidth().height(50.dp),
                enabled = !isLoading && titulo.isNotBlank(),
                colors = ButtonDefaults.buttonColors(containerColor = ColorPrimario)
            ) {
                if (isLoading) CircularProgressIndicator(modifier = Modifier.size(24.dp), color = Color.White)
                else Text("Guardar en Base de Datos", color = Color.White, fontWeight = FontWeight.Bold)
            }
        }
    }
}
