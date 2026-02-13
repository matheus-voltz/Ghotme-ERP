import SwiftUI

struct WatchOS: Codable, Identifiable {
    let id: Int
    let client: String
    let vehicle: String
    let status: String
}

struct WatchDashboardResponse: Codable {
    let user_name: String
    let orders: [WatchOS]
}

class WatchViewModel: ObservableObject {
    @Published var orders: [WatchOS] = []
    @Published var userName: String = ""
    @Published var isLoading = false
    
    // Altere para o seu domínio ou IP do computador
    let baseURL = "http://10.0.0.166:8000/api" 
    let token = "SEU_TOKEN_AQUI" // Em produção, o iPhone envia isso para o Watch automaticamente
    
    func fetchData() {
        guard let url = URL(string: "\(baseURL)/watch/dashboard") else { return }
        var request = URLRequest(url: url)
        request.setValue("Bearer \(token)", forHTTPHeaderField: "Authorization")
        request.setValue("application/json", forHTTPHeaderField: "Accept")
        
        isLoading = true
        URLSession.shared.dataTask(with: request) { data, _, _ in
            if let data = data {
                if let decoded = try? JSONDecoder().decode(WatchDashboardResponse.self, from: data) {
                    DispatchQueue.main.async {
                        self.orders = decoded.orders
                        self.userName = decoded.user_name
                        self.isLoading = false
                    }
                }
            }
        }.resume()
    }
}

struct ContentView: View {
    @StateObject var viewModel = WatchViewModel()
    
    var body: some View {
        NavigationStack {
            VStack {
                if viewModel.isLoading {
                    ProgressView()
                } else {
                    List(viewModel.orders) { os in
                        VStack(alignment: .leading) {
                            HStack {
                                Text("#\(os.id)")
                                    .fontWeight(.bold)
                                    .foregroundColor(.blue)
                                Spacer()
                                Circle()
                                    .fill(os.status == "running" ? .cyan : .orange)
                                    .frame(width: 8, height: 8)
                            }
                            Text(os.vehicle)
                                .font(.caption)
                            Text(os.client)
                                .font(.system(size: 10))
                                .foregroundColor(.gray)
                        }
                    }
                }
            }
            .navigationTitle("Ghotme")
            .onAppear { viewModel.fetchData() }
        }
    }
}