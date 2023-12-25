import networkx as nx
import sys

filename = 'debug.txt' if len(sys.argv) > 0 and sys.argv[1] == 'debug' else 'input.txt'

g = nx.Graph()

for line in open(filename):
    left, right = line.split(":")
    for node in right.strip().split():
        g.add_edge(left, node)
        g.add_edge(node, left)

g.remove_edges_from(nx.minimum_edge_cut(g))
a, b = nx.connected_components(g)

print(len(a) * len(b))
