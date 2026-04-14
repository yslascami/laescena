package org.example.project

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.*
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.foundation.Image
import org.jetbrains.compose.resources.painterResource
import laescena.composeapp.generated.resources.Res
import laescena.composeapp.generated.resources.con1
import laescena.composeapp.generated.resources.con2

@Composable
fun EventosScreen() {
    Column(
        modifier = Modifier
            .fillMaxSize()
            .padding(horizontal=38.dp)
            .verticalScroll(rememberScrollState()),
        verticalArrangement = Arrangement.Top,
        horizontalAlignment = Alignment.CenterHorizontally
    ) {
        Spacer(modifier = Modifier.height(38.dp))
        Text(text = "Eventos en existencia", style = MaterialTheme.typography.headlineLarge)
        Spacer(modifier = Modifier.height(38.dp))

        Text(text="Anper Bajo el radar",
            modifier = Modifier.fillMaxWidth(),
            textAlign = TextAlign.Left,
            style = MaterialTheme.typography.headlineMedium)
        Spacer(modifier = Modifier.height(10.dp))
        Image(
            painter = painterResource(Res.drawable.con1),
            contentDescription = "Anper Bajo el radar",
            modifier = Modifier.size(300.dp)
        )
        Spacer(modifier = Modifier.height(8.dp))
        Text(text = "Anper en vivo desde el centro cultural Ricardo Garibay el 25 de marzo del 2026...",
            modifier = Modifier.fillMaxWidth(),
            textAlign = TextAlign.Left,
            style = MaterialTheme.typography.bodyMedium)

        Spacer(modifier = Modifier.height(38.dp))

        Text(text="¿Quién es ese pxndjx?",
            modifier = Modifier.fillMaxWidth(),
            style = MaterialTheme.typography.headlineMedium)
        Spacer(modifier = Modifier.height(10.dp))
        Image(
            painter = painterResource(Res.drawable.con2),
            contentDescription = "El pendejo en vivo",
            modifier = Modifier.size(300.dp)
        )
        Spacer(modifier = Modifier.height(8.dp))
        Text(text = "Gerardo Bracho trae a nosotros su proyecto como solista...",
            modifier = Modifier.fillMaxWidth(),
            textAlign = TextAlign.Left,
            style = MaterialTheme.typography.bodyMedium)
    }
}
