package org.example.project

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.launch

class PortafolioViewModel : ViewModel() {
    private val api = Apiservice()

    private val _portafolioItems = MutableStateFlow<List<Portafolio>>(emptyList())
    val portafolioItems: StateFlow<List<Portafolio>> get() = _portafolioItems

    private val _isLoading = MutableStateFlow(false)
    val isLoading: StateFlow<Boolean> get() = _isLoading

    private val _error = MutableStateFlow("")
    val error: StateFlow<String> get() = _error

    private val _uploadSuccess = MutableStateFlow(false)
    val uploadSuccess: StateFlow<Boolean> get() = _uploadSuccess

    fun cargarPortafolio(artistaId: Int) {
        viewModelScope.launch {
            _isLoading.value = true
            try {
                val resultado = api.getPortafolio(artistaId)
                _portafolioItems.value = resultado
                _error.value = ""
            } catch (e: Exception) {
                _error.value = "Error al cargar el portafolio"
            } finally {
                _isLoading.value = false
            }
        }
    }

    fun crearNuevoPortafolio(artistaId: Int, nombreArtista: String, titulo: String, descripcion: String, tipo: String, archivo: String) {
        viewModelScope.launch {
            _isLoading.value = true
            try {
                val nuevo = Portafolio(
                    artista_id = artistaId,
                    nombre_artista = nombreArtista,
                    titulo = titulo,
                    descripcion = descripcion,
                    tipo = tipo,
                    archivo = archivo
                )
                val response = api.crearPortafolio(nuevo)
                if (response.success) {
                    _uploadSuccess.value = true
                } else {
                    _error.value = response.message
                }
            } catch (e: Exception) {
                _error.value = "Error al subir portafolio"
            } finally {
                _isLoading.value = false
            }
        }
    }

    fun resetUploadSuccess() {
        _uploadSuccess.value = false
    }
}
