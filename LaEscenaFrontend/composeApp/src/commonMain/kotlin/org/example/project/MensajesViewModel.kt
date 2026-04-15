package org.example.project

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.launch

class MensajesViewModel : ViewModel() {
    private val api = Apiservice()

    private val _mensajes = MutableStateFlow<List<Mensaje>>(emptyList())
    val mensajes: StateFlow<List<Mensaje>> get() = _mensajes

    private val _isLoading = MutableStateFlow(false)
    val isLoading: StateFlow<Boolean> get() = _isLoading

    private val _error = MutableStateFlow("")
    val error: StateFlow<String> get() = _error

    private val _envioExitoso = MutableStateFlow(false)
    val envioExitoso: StateFlow<Boolean> get() = _envioExitoso

    fun cargarMensajes(artistaId: Int) {
        viewModelScope.launch {
            _isLoading.value = true
            try {
                val resultado = api.getMensajes(artistaId)
                _mensajes.value = resultado
                _error.value = ""
            } catch (e: Exception) {
                _error.value = "Error al cargar mensajes"
            } finally {
                _isLoading.value = false
            }
        }
    }

    fun enviarNuevoMensaje(artistaId: Int, remitente: String, asunto: String, contenido: String) {
        viewModelScope.launch {
            _isLoading.value = true
            try {
                val nuevoMensaje = Mensaje(
                    artista_id = artistaId,
                    remitente = remitente,
                    asunto = asunto,
                    mensaje = contenido
                )
                val response = api.enviarMensaje(nuevoMensaje)
                if (response.success) {
                    _envioExitoso.value = true
                    cargarMensajes(artistaId)
                } else {
                    _error.value = response.message
                }
            } catch (e: Exception) {
                _error.value = "Error al enviar mensaje"
            } finally {
                _isLoading.value = false
            }
        }
    }

    fun resetEnvioStatus() {
        _envioExitoso.value = false
    }
}
