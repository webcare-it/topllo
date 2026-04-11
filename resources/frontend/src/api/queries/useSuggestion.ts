import { apiClient } from "@/lib/api-client";
import { useQuery } from "@tanstack/react-query";
import type { QueryType } from "../utils/type";

export const useSearchSuggestion = (): QueryType => {
  const { data, isLoading, error } = useQuery({
    queryKey: ["get_search_suggestion"],
    queryFn: async () => {
      await new Promise((resolve) => setTimeout(resolve, 3000));
      const response = await apiClient.get("/get-search-suggestions");
      return response.data;
    },
    staleTime: 1000 * 60 * 60,
    gcTime: 1000 * 60 * 60,
  });

  return { data, isLoading, error };
};
