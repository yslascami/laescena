package org.example.project

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.launch

class ArtistasViewModel : ViewModel() {
    private val api = Apiservice()

    private val _artistas = MutableStateFlow<List<Artista>>(emptyList())
    val artistas: StateFlow<List<Artista>> get() = _artistas

    private val _artistaSeleccionado = MutableStateFlow<Artista?>(null)
    val artistaSeleccionado: StateFlow<Artista?> get() = _artistaSeleccionado

    private val _isLoading = MutableStateFlow(false)
    val isLoading: StateFlow<Boolean> get() = _isLoading

    private val _error = MutableStateFlow("")
    val error: StateFlow<String> get() = _error

    fun cargarArtistas() {
        viewModelScope.launch {
            _isLoading.value = true
            val resultado = api.getArtistas()
            if (resultado.isEmpty()) {
                _error.value = "No se encontraron artistas"
            } else {
                _artistas.value = resultado
                _error.value = ""
            }
            _isLoading.value = false
        }
    }

    fun cargarArtista(id: Int) {
        viewModelScope.launch {
            _isLoading.value = true
            val resultado = api.getArtista(id)
            if (resultado == null) {
                _error.value = "Artista no encontrado"
            } else {
                _artistaSeleccionado.value = resultado
                _error.value = ""
            }
            _isLoading.value = false
        }
    }
}