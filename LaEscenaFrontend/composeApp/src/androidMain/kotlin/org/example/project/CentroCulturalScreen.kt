package org.example.project

import androidx.compose.foundation.Image
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowForward
import androidx.compose.material.icons.automirrored.filled.ExitToApp
import androidx.compose.material3.*
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.navigation.NavController
import org.jetbrains.compose.resources.painterResource
import laescena.composeapp.generated.resources.Res
import laescena.composeapp.generated.resources.cc

@Composable
fun CentroCulturalScreen() {
    Column(
        modifier = Modifier
            .fillMaxSize()
            .verticalScroll(rememberScrollState())
            .padding(24.dp),
        horizontalAlignment = Alignment.CenterHorizontally
    ) {
        Spacer(modifier = Modifier.height(24.dp))
        Image(
            painter = painterResource(Res.drawable.cc),
            contentDescription = "Centro Cultural Ricardo Garibay",
            modifier = Modifier.fillMaxWidth().height(220.dp)
        )
        Spacer(modifier = Modifier.height(20.dp))
        Text(
            text = "Centro cultural Ricardo Garibay",
            fontSize = 24.sp,
            fontWeight = FontWeight.Bold,
            color = Color(0xFF212121),
            textAlign = TextAlign.Center
        )
        Spacer(modifier = Modifier.height(12.dp))
        Text(
            text = "El centro cultural Ricardo Garibay es un espacio dedicado a la promoción...",
            fontSize = 15.sp,
            color = Color(0xFF212121),
            textAlign = TextAlign.Center
        )
    }
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun CentroCulturalDashboardScreen(navController: NavController) {
    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Centro Cultural", color = ColorPrimario, fontWeight = FontWeight.Bold) },
                actions = {
                    IconButton(onClick = { navController.navigate("home") { popUpTo(0) } }) {
                        Icon(Icons.AutoMirrored.Filled.ExitToApp, contentDescription = "Cerrar Sesión", tint = ColorPrimario)
                    }
                },
                colors = TopAppBarDefaults.topAppBarColors(containerColor = ColorFondo)
            )
        },
        containerColor = ColorFondo
    ) { padding ->
        Column(
            modifier = Modifier.fillMaxSize().padding(padding).padding(16.dp)
        ) {
            Text(text = "Gestión del centro cultural", color = ColorTextoSecundario)
            Spacer(modifier = Modifier.height(20.dp))
            LazyColumn(verticalArrangement = Arrangement.spacedBy(12.dp)) {
                item { DashboardCard("Panel", onClick = { /* Panel */ }) }
                item { DashboardCard("Eventos", onClick = { navController.navigate("eventos") }) }
                item { DashboardCard("Galeria", onClick = { navController.navigate("galerias") }) }
                item { DashboardCard("Ver catálogo", onClick = { navController.navigate("catalogo") }) }
            }
        }
    }
}
