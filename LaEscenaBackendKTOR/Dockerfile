FROM gradle:8.11-jdk21 AS build
WORKDIR /app
COPY LaEscenaBackendKTOR/ .
RUN gradle buildFatJar --no-daemon

FROM eclipse-temurin:21-jre
WORKDIR /app
COPY --from=build /app/build/libs/app.jar app.jar
EXPOSE 8080
CMD ["java", "-jar", "app.jar"]