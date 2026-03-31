package org.example.project

import androidx.compose.material3.MaterialTheme
import androidx.compose.runtime.Composable
import androidx.lifecycle.viewmodel.compose.viewModel

@Composable
fun App() {
    MaterialTheme {
        // En un proyecto con navegación, solemos dejar que el NavHost 
        // maneje las pantallas. Esta función App() puede estar vacía 
        // o contener la estructura base si no usas MainActivity para todo.
        
        // Si usas MainActivity.kt con NavHost, este archivo App.kt 
        // podría no ser necesario o usarse solo para temas.
    }
}
