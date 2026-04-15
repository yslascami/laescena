FROM gradle:8.11-jdk17 AS build
WORKDIR /app
COPY . .
RUN gradle shadowJar --no-daemon

FROM openjdk:17-jdk-slim
WORKDIR /app
COPY --from=build /app/build/libs/*.jar app.jar
EXPOSE 8080
ENTRYPOINT ["java", "-jar", "app.jar"]